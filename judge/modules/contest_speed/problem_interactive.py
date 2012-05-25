import os
import signal
from string import Template
import subprocess
import tempfile
import time
import traceback
import sys

import utils
from utils import GradingException
import common

def _run_tests(task, team_filebase, team_extension, team_filename, metadata, verbose):
  '''Compile interactive grader, and execute judge test cases.'''
  
  if not task['problem_metadata']['grader']['valid']:
    utils.progress('No grader found.')
    raise Exception('No interactive grader found!')
  
  try:
    payload = task['problem_metadata']['grader']['src']
    grader_filebase =  task['problem_metadata']['grader']['filebase']
    grader_extension = task['problem_metadata']['grader']['extension']
    grader_filename = grader_filebase + '.' + grader_extension
    utils.compile(payload, grader_filebase, grader_extension, grader_filename)
  except Exception, e:
    utils.progress('Internal error when compiling grader')
    raise
  
  time_limit = utils.languages[team_extension]['executer_time_limit']
  num_test_cases = len(task['problem_metadata']['judge_io'])

  for index, test_case in enumerate(task['problem_metadata']['judge_io']):
    input_fd, input_filename = tempfile.mkstemp()
    try:
      with open(input_filename, 'w') as input_file:
        input_file.write(test_case['input'])
        input_file.flush()
        input_file.close()
      
        team_input_fd, grader_output_fd = os.pipe()
        grader_input_fd, team_output_fd = os.pipe()
        
        def team_init():
          os.setsid()
          os.dup2(team_input_fd, 0)
          os.dup2(team_output_fd, 1)
          os.execvp(team_executer_cmd[0], team_executer_cmd)
          
        def grader_init():
          os.setsid()
          os.dup2(grader_input_fd, 0)
          os.dup2(grader_output_fd, 1)
          os.execvp(grader_executer_cmd[0], grader_executer_cmd)
        
        team_executer_cmd = utils.languages[team_extension]['executer'].substitute(src_filebase=team_filebase, src_filename=team_filename).split()
        grader_executer_cmd = utils.languages[grader_extension]['executer'].substitute(src_filebase=grader_filebase, src_filename=grader_filename).split()
        grader_executer_cmd.append(input_filename)
        
        if verbose:
          stderr = tempfile.TemporaryFile(bufsize=10485760)
        else:
          stderr = open(os.devnull, 'w')
        
        team_executer = subprocess.Popen(team_executer_cmd, stderr=stderr, preexec_fn=team_init)
        grader_executer = subprocess.Popen(grader_executer_cmd, stderr=open(os.devnull, 'w'), preexec_fn=grader_init)
        start_time = time.time()
        while grader_executer.poll() is None and time.time() - start_time <= time_limit:
          time.sleep(0.5)
        
        grader_finished = grader_executer.poll() is not None
        if not grader_finished:
          if verbose:
            utils.progress('Grader executable did not finish; killing PID %d after %d seconds' % (grader_executer.pid, time.time() - start_time))
          os.killpg(grader_executer.pid, signal.SIGKILL)
        if team_executer.poll() is None:
          if verbose:
            utils.progress('Team executable did not finish; killing PID %d after %d seconds' % (team_executer.pid, time.time() - start_time))
          os.killpg(team_executer.pid, signal.SIGKILL)
          if not grader_finished:
            raise GradingException('Time limit exceeded')
          else:
            raise GradingException('Incorrect output')
        if team_executer.returncode != 0:
          if verbose:
            utils.progress('Team executable gave non-zero return code: %d' % executer.returncode)
            stderr.seek(0)
            print stderr.read()
          raise GradingException('Run time error')
        if not grader_finished:
          raise GradingException('Time limit exceeded')
        if grader_executer.returncode != 0:
          if verbose:
            utils.progress('Grader executable gave non-zero return code: %d' % grader_executer.returncode)
          raise GradingException('Incorrect output')
        utils.progress('Passed %2d / %2d' % (index + 1, num_test_cases))
    finally:
      os.remove(input_filename)
  utils.progress('Correct')
  return True  
  
def grade(q, task, verbose):
  '''Grades an interactive submission.'''

  return common.grade(q, task, verbose, _run_tests)