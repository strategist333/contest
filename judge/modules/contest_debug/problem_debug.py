import modules.contest_debug.compile
from modules.contest_debug.compile import GradingException
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
import pipes

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

def run_tests(task, team_select, team_correct, team_wrong, metadata):
  '''Compile judge cleanly and execute the test case on the judge. '''
  result = False

  time_limit = 5
  try:
    payload = task['problem_metadata']['grader']['src']
    grader_filebase = task['problem_metadata']['grader']['filebase']
    grader_extension = task['problem_metadata']['grader']['extension']
    grader_filename = grader_filebase + '.' + grader_extension
    modules.contest_debug.compile.compile(payload, grader_filebase, grader_extension, grader_filename)

    correct_status = False   # Was the supposedly correct input actually correct?
    wrong_status = False     # Was the supposedly wrong input actually wrong?

    grader_executer_cmd = languages[grader_extension]['executer'].substitute(src_filebase=grader_filebase, src_filename=grader_filename).split()

    # Run grader on team-submitted "correct" data
    utils.progress("Running on good input")

    grader_executer = subprocess.Popen(grader_executer_cmd, stdin=subprocess.PIPE, stdout=open(os.devnull, 'w'), stderr=open(os.devnull, 'w'), preexec_fn=os.setsid, close_fds=True)    
    start_time = time.time()
    grader_executer.communicate(team_correct)
    while grader_executer.poll() is None and time.time() - start_time < time_limit:
      time.sleep(0.5)
    grader_finished = grader_executer.poll() is not None
    if not grader_finished:
      os.killpg(grader_executer.pid, signal.SIGKILL)
    elif grader_executer.returncode == 100:
      correct_status = True

    # Run grader on team-submitted "wrong" data
    utils.progress("Running on bad input")

    grader_executer = subprocess.Popen(grader_executer_cmd, stdin=subprocess.PIPE, stdout=open(os.devnull, 'w'), stderr=open(os.devnull, 'w'), preexec_fn=os.setsid, close_fds=True)
    start_time = time.time()
    grader_executer.communicate(team_wrong)
    while grader_executer.poll() is None and time.time() - start_time < time_limit:
      time.sleep(0.5) 
    grader_finished = grader_executer.poll() is not None
    if not grader_finished:
      os.killpg(grader_executer.pid, signal.SIGKILL)
    elif grader_executer.returncode == 200:
      wrong_status = True

    # correct
    if task['division_metadata']['type'] == 'correct':
      if correct_status:
        result = True

    # wrong  
    if task['division_metadata']['type'] == 'wrong':
      if wrong_status:
        result = True

    # sometimes  
    if task['division_metadata']['type'] == 'sometimes':
      if correct_status and wrong_status:
        result = True

  except Exception, e:
    utils.progress('Internal error when compiling grader.')
    raise Exception(e)

  if result:
    utils.progress('Correct')
  else:
    utils.progress('Incorrect')

  return result


def grade(q, task, **kwargs):
  # Grades a debugging submission
  return modules.contest_debug.compile.grade(q, task, run_tests, **kwargs)
