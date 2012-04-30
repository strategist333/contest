import exceptions
import os
import signal
from string import Template
import subprocess
import shutil
import time

import utils

class GradingException(exceptions.Exception):
	def __init__(self, msg):
		super(GradingException, self).__init__(msg)

languages = {
  'c'    : dict(compiler=Template('gcc -o $src_filebase $src_filename'),
                check_for=Template('$src_filebase'),
                compile_time_limit=10),
  'cc'   : dict(compiler=Template('g++ -o $src_filebase $src_filename'),
                check_for=Template('$src_filebase'),
                compile_time_limit=10),
  'cpp'  : dict(compiler=Template('g++ -o $src_filebase $src_filename'),
                check_for=Template('$src_filebase'),
                compile_time_limit=10),
  'java' : dict(compiler=Template('javac -cp . $src_filename'),
                check_for=Template('$src_filebase.class'),
                compile_time_limit=10),
  'py'   : dict(compiler=Template('python -mpy_compile $src_filename'),
                check_for=Template('$src_filebase.pyc'),
                compile_time_limit=10),
}


def grade(q, task, callback, **kwargs):
  '''Sets up a file-based submission for grading, then calls a callback for grading.
  
  Sets up a sandbox directory, compiles the program, and calls the callback function for grading.'''

  sandbox_dir = utils.setup_tmpdir()
  os.chdir(sandbox_dir)

  payload = task['payload']
  src_filebase =  task['alias']
  src_extension = task['run_metadata']['extension']
  src_filename = src_filebase + '.' + src_extension

  correct = False
  metadata = {}
  
  try:
    with open(src_filename, 'w+') as src_file:
      src_file.write(payload)
      src_file.flush()
      src_file.close()

      utils.progress('Compiling')
      compiler_cmd = languages[src_extension]['compiler'].substitute(src_filebase=src_filebase, src_filename=src_filename)
      if compiler_cmd is not None:
        time_limit = languages[src_extension]['compile_time_limit']
        compiler = subprocess.Popen(compiler_cmd.split(), stdout=open(os.devnull, 'w'), stderr=subprocess.STDOUT, preexec_fn=os.setsid)
        start_time = time.time()
        while compiler.poll() is None and (time.time() - start_time < time_limit):
          time.sleep(0.5)
        if compiler.poll() is None:
          os.killpg(compiler.pid, signal.SIGKILL)
          raise GradingException('Compile time limit exceeded')
        if compiler.returncode != 0:
          raise GradingException('Compiler error')

      check_for = languages[src_extension]['check_for'].substitute(src_filebase=src_filebase, src_filename=src_filename)

      if not os.path.exists(check_for):
        raise GradingException('Compiler error')
        
      correct = callback(task, src_filebase, src_extension, src_filename, metadata)

  except GradingException, e:
    utils.progress(e.message)
    metadata['error'] = e.message
  except Exception, e:
    utils.progress('Internal exception!')
    raise e
  finally:
    shutil.rmtree(sandbox_dir)

  q.put({'correct' : correct, 'metadata' : metadata})