import modules.contest_speed.compile
from modules.contest_speed.compile import GradingException
import exceptions
import os
import signal
from string import Template
import subprocess
import tempfile
import time
import traceback
import sys

import utils

languages = {
  'c'    : dict(executer=Template('./$src_filebase'),
                executer_time_limit=2),
  'cc'   : dict(executer=Template('./$src_filebase'),
                executer_time_limit=2),
  'cpp'  : dict(executer=Template('./$src_filebase'),
                executer_time_limit=2),
  'java' : dict(executer=Template('java $src_filebase'),
                executer_time_limit=3),
  'py'   : dict(executer=Template('python $src_filebase.pyc'),
                executer_time_limit=4)
}

def run_tests(task, team_filebase, team_extension, team_filename, metadata):
  '''Compile interactive grader, and execute judge test cases.'''
  
  try:
    payload = task['problem_metadata']['grader']['src']
    grader_filebase =  task['problem_metadata']['grader']['filebase']
    grader_extension = task['problem_metadata']['grader']['extension']
    grader_filename = grader_filebase + '.' + grader_extension
    modules.contest_speed.compile.compile(payload, grader_filebase, grader_extension, grader_filename)
  except Exception, e:
    utils.progress('Internal error when compiling grader')
    raise Exception(e)
  
  time_limit = languages[team_extension]['executer_time_limit']
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
        
        team_executer_cmd = languages[team_extension]['executer'].substitute(src_filebase=team_filebase, src_filename=team_filename).split()
        grader_executer_cmd = languages[grader_extension]['executer'].substitute(src_filebase=grader_filebase, src_filename=grader_filename).split()
        grader_executer_cmd.append(input_filename)
        
        team_executer = subprocess.Popen(team_executer_cmd, stderr=open(os.devnull, 'w'), preexec_fn=team_init)
        grader_executer = subprocess.Popen(grader_executer_cmd, stderr=open(os.devnull, 'w'), preexec_fn=grader_init)
        start_time = time.time()
        while grader_executer.poll() is None and time.time() - start_time < time_limit:
          time.sleep(0.5)
        
        grader_finished = grader_executer.poll() is not None
        if not grader_finished:
          os.killpg(grader_executer.pid, signal.SIGKILL)
        if team_executer.poll() is None:
          os.killpg(team_executer.pid, signal.SIGKILL)
          raise GradingException('Time limit exceeded')
        if team_executer.returncode != 0:
          raise GradingException('Run time error')
        if not grader_finished:
          raise GradingException('Time limit exceeded')
        if grader_executer.returncode != 0:
          raise GradingException('Incorrect output')
        utils.progress('Passed %2d / %2d' % (index + 1, num_test_cases))
    finally:
      os.remove(input_filename)
  utils.progress('Correct')
  return True
  
  
  
def grade(q, task, **kwargs):
  '''Grades an interactive submission.'''

  return modules.contest_speed.compile.grade(q, task, run_tests, **kwargs)