import exceptions
import os
import shutil
import signal
from string import Template
import subprocess
import time
import tempfile

import utils
from utils import GradingException
import common

def run_tests(task, team_filebase, team_extension, team_filename, metadata):
  '''Execute judge test cases.'''

  time_limit = utils.languages[team_extension]['executer_time_limit']
  num_test_cases = len(task['problem_metadata']['judge_io'])

  for index, test_case in enumerate(task['problem_metadata']['judge_io']):
    executer_cmd = utils.languages[team_extension]['executer'].substitute(src_filebase=team_filebase, src_filename=team_filename).split()
    
    stdin = tempfile.TemporaryFile(bufsize=10485760)
    stdin.write(test_case['input'])
    stdin.flush()
    stdin.seek(0)

    stdout = tempfile.TemporaryFile(bufsize=10485760)
    
    executer = subprocess.Popen(executer_cmd, stdin=stdin, stdout=stdout, stderr=open(os.devnull, 'w'), preexec_fn=os.setsid, close_fds=True)
    start_time = time.time()
    while executer.poll() is None and (time.time() - start_time < time_limit):
      time.sleep(0.5)
    if executer.poll() is None:
      os.killpg(executer.pid, signal.SIGKILL)
      raise GradingException('Time limit exceeded')
    if executer.returncode != 0:
      raise GradingException('Run time error %d' % executer.returncode)
    
    stdout.seek(0)
    team_output = stdout.read()
   
    if map(lambda line: line.strip(), team_output.split('\n')) != map(lambda line: line.strip(), test_case['output'].split('\n')):
      raise GradingException('Incorrect output')
    utils.progress('Passed %2d / %2d' % (index + 1, num_test_cases))
  utils.progress('Correct')
  return True

def grade(q, task, **kwargs):
  '''Grades a speed submission.'''

  return common.setup(q, task, run_tests, **kwargs)