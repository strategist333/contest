#!/usr/bin/python
import exceptions
import json
import os
import urllib2
import subprocess
import sys
import time
from string import Template

import config

def call(**kwargs):
  '''Helper function to POST a json call to config.handle_url.'''
  req = urllib2.urlopen(config.handle_url, json.dumps(kwargs, separators=(',',':')))
  if not req:
    raise Exception('Failed to open connection.')
  js = req.read()
  try:
    return json.loads(js)
  except Exception as e:
    print js
    raise e  

def progress(s):
  '''Outputs a progress indication.'''
  
  sys.stdout.write('\b' * progress.last_len)
  sys.stdout.write(' ' * progress.last_len)
  sys.stdout.write('\b' * progress.last_len)
  
  sys.stdout.write(s)
  sys.stdout.flush()
  progress.last_len = len(s)
  
def reset_progress():
  progress.last_len = 0

languages = {
  'c'    : dict(compiler=Template('gcc -o $src_filebase $src_filename'),
                check_for=Template('$src_filebase'),
                compile_time_limit=10,
                executer=Template('./$src_filebase'),
                executer_time_limit=2),
  'cc'   : dict(compiler=Template('g++ -o $src_filebase $src_filename'),
                check_for=Template('$src_filebase'),
                compile_time_limit=10,
                executer=Template('./$src_filebase'),
                executer_time_limit=2),
  'cpp'  : dict(compiler=Template('g++ -o $src_filebase $src_filename'),
                check_for=Template('$src_filebase'),
                compile_time_limit=10,
                executer=Template('./$src_filebase'),
                executer_time_limit=2),
  'java' : dict(compiler=Template('javac -cp . $src_filename'),
                check_for=Template('$src_filebase.class'),
                compile_time_limit=10,
                executer=Template('java $src_filebase'),
                executer_time_limit=3),
  'py'   : dict(compiler=Template('python -mpy_compile $src_filename'),
                check_for=Template('$src_filebase.pyc'),
                compile_time_limit=10,
                executer=Template('python $src_filebase.pyc'),
                executer_time_limit=4)
}

def compile(payload, src_filebase, src_extension, src_filename):
  '''Compile a payload in the current working directory.'''

  with open(src_filename, 'w+') as src_file:
    src_file.write(payload)
    src_file.flush()
    src_file.close()

    progress('Compiling ' + src_filename)

    compiler_cmd = languages[src_extension]['compiler'].substitute(src_filebase=src_filebase, src_filename=src_filename)
    if compiler_cmd is not None:
      compiler_cmd = compiler_cmd.split()
      time_limit = languages[src_extension]['compile_time_limit']
      compiler = subprocess.Popen(compiler_cmd, stdout=open(os.devnull, 'w'), stderr=subprocess.STDOUT, preexec_fn=os.setsid)
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

class GradingException(exceptions.Exception):
	def __init__(self, msg):
		super(GradingException, self).__init__(msg)