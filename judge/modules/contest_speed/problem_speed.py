import multiprocessing
import os
import signal
import pwd
import grp
from string import Template
import subprocess
import shutil
import time
import sys

import config
import utils

languages = {
  'c'    : dict(compiler=Template('gcc -o $src_filebase $src_filename'),
                check_for=Template('$src_filebase'),
                executer=Template('./$src_filebase'),
                time_limit=1),
  'cc'   : dict(compiler=Template('g++ -o $src_filebase $src_filename'),
                check_for=Template('$src_filebase'),
                executer=Template('./$src_filebase'),
                time_limit=1),
  'cpp'  : dict(compiler=Template('g++ -o $src_filebase $src_filename'),
                check_for=Template('$src_filebase'),
                executer=Template('./$src_filebase'),
                time_limit=1),
  'java' : dict(compiler=Template('javac -cp . $src_filename'),
                check_for=Template('$src_filebase.class'),
                executer=Template('java $src_filebase'),
                time_limit=2),
  'py'   : dict(compiler=Template('python -mpy_compile $src_filename'),
                check_for=Template('$src_filebase.pyc'),
                executer=Template('python $src_filebase.pyc'),
                time_limit=3)
}


def grade(q, task, **kwargs):
  '''Grades a submission.'''

  sandbox_dir = utils.setup_tmpdir()
  os.chdir(sandbox_dir)

  payload = task['payload']
  src_filebase =  task['alias']
  src_extension = task['run_metadata']['extension']
  src_filename = src_filebase + '.' + src_extension

  correct = False
  metadata = {}
  
  devnull = open(os.devnull, 'w')

  try:
    with open(src_filename, 'w+') as src_file:
      src_file.write(payload)
      src_file.flush()
      src_file.close()

      utils.progress('Compiling')
      compiler_cmd = languages[src_extension]['compiler'].substitute(src_filebase=src_filebase, src_filename=src_filename)
      if compiler_cmd is not None:
        compiler = subprocess.Popen(compiler_cmd.split(), stdout=devnull, stderr=subprocess.STDOUT)
        compiler.wait()
        if compiler.returncode != 0:
          raise Exception('Compiler error')

      check_for = languages[src_extension]['check_for'].substitute(src_filebase=src_filebase, src_filename=src_filename)

      if not os.path.exists(check_for):
        raise Exception('Compiler error')

      time_limit = languages[src_extension]['time_limit']
      num_test_cases = len(task['problem_metadata']['judge_io'])

      for index, test_case in enumerate(task['problem_metadata']['judge_io']):
        executer_cmd = languages[src_extension]['executer'].substitute(src_filebase=src_filebase, src_filename=src_filename)

        executer = subprocess.Popen(executer_cmd.split(), stdin=subprocess.PIPE, stdout=subprocess.PIPE, stderr=devnull, preexec_fn=os.setsid, close_fds=True)
        executer.stdin.write(test_case['input'])
        executer.stdin.flush()
        executer.stdin.close()
        start_time = time.time()
        while executer.poll() is None and (time.time() - start_time < time_limit):
          time.sleep(0.5)
        if executer.poll() is None:
          os.killpg(executer.pid, signal.SIGKILL)
          raise Exception('Time limit exceeded')
        if executer.returncode != 0:
          raise Exception('Run time error')
        team_output = executer.stdout.read()
        if map(lambda line: line.strip(), team_output.split('\n')) != map(lambda line: line.strip(), test_case['output'].split('\n')):
          raise Exception('Incorrect output')
        utils.progress('Passed %2d / %2d' % (index + 1, num_test_cases))
      correct = True
  except Exception, e:
    utils.progress(e.message)
    metadata['error'] = e.message
  finally:
    shutil.rmtree(sandbox_dir)

  q.put({'correct' : correct, 'metadata' : metadata})