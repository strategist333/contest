import difflib
import os
import signal
from string import Template
import subprocess
import sys
import time
import tempfile

import utils
from utils import GradingException
import common

def _run_tests(task, team_filebase, team_extension, team_filename, metadata, verbose):
  '''Execute judge test cases.'''

  time_limit = utils.languages[team_extension]['executer_time_limit'] * task['problem_metadata']['time_multiplier'] 
  num_test_cases = len(task['problem_metadata']['judge_io'])
  
  for index, test_case in enumerate(task['problem_metadata']['judge_io']):
    executer_cmd = utils.languages[team_extension]['executer'].substitute(src_filebase=team_filebase, src_filename=team_filename).split()
    
    stdin = tempfile.TemporaryFile(bufsize=10485760)
    stdin.write(test_case['input'])
    stdin.flush()
    stdin.seek(0)

    stdout = tempfile.TemporaryFile(bufsize=10485760)
    
    if verbose:
      stderr = tempfile.TemporaryFile(bufsize=10485760)
    else:
      stderr = open(os.devnull, 'w')
    
    executer = subprocess.Popen(executer_cmd, stdin=stdin, stdout=stdout, stderr=stderr, preexec_fn=os.setsid, close_fds=True)
    start_time = time.time()
    while executer.poll() is None and (time.time() - start_time <= time_limit):
      time.sleep(0.5)
    if executer.poll() is None:
      if verbose:
        utils.progress('Team executable did not finish; killing PID %d after %d seconds' % (executer.pid, time.time() - start_time))
      os.killpg(executer.pid, signal.SIGKILL)
      raise GradingException('Time limit exceeded')
    if executer.returncode != 0:
      if verbose:
        utils.progress('Team executable gave non-zero return code: %d' % executer.returncode)
        stderr.seek(0)
        print stderr.read()
      raise GradingException('Run time error')
    
    stdout.seek(0)
    team_output = stdout.read()
   
    team_output_lines = map(lambda line: line.strip(), team_output.splitlines())
    judge_output_lines = map(lambda line: line.strip(), test_case['output'].splitlines())
    if team_output_lines != judge_output_lines:
      if verbose:
        utils.progress('Failed %2d / %2d' % (index + 1, num_test_cases))
        diff = difflib.Differ()
        sys.stdout.writelines(list(diff.compare(map(lambda line: line + '\n', team_output_lines), map(lambda line: line + '\n', judge_output_lines))))
      raise GradingException('Incorrect output')
    utils.progress('Passed %2d / %2d' % (index + 1, num_test_cases))
  utils.progress('Correct')
  return True

def grade(q, task, verbose):
  '''Grades a speed submission.'''

  return common.grade(q, task, verbose, _run_tests)