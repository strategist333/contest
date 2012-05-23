import exceptions
import json
import os
import shutil
import signal
from string import Template
import subprocess
import time
import tempfile

import utils

def run_test(grader_executer_cmd, team_input, desired_return_code):
  '''Run an individual test and return whether it was accepted by the grader.'''
  time_limit = 5

  stdin = tempfile.TemporaryFile(bufsize=10485760)
  stdin.write(team_input)
  stdin.flush()
  stdin.seek(0)
  
  grader_executer = subprocess.Popen(grader_executer_cmd, stdin=stdin, stdout=open(os.devnull, 'w'), stderr=open(os.devnull, 'w'), preexec_fn=os.setsid, close_fds=True)    
  start_time = time.time()
  while grader_executer.poll() is None and time.time() - start_time < time_limit:
    time.sleep(0.5)
  grader_finished = grader_executer.poll() is not None
  if grader_executer.poll() is None:
    os.killpg(grader_executer.pid, signal.SIGKILL)
    return False
  else:
    return (grader_executer.returncode == desired_return_code)

def run_tests(task, team_select, team_correct, team_wrong):
  '''Compile judge and run both test cases on the judge. '''
  
  payload = task['problem_metadata']['grader']['src']
  grader_filebase = task['problem_metadata']['grader']['filebase']
  grader_extension = task['problem_metadata']['grader']['extension']
  grader_filename = grader_filebase + '.' + grader_extension
  utils.compile(payload, grader_filebase, grader_extension, grader_filename)
  grader_executer_cmd = utils.languages[grader_extension]['executer'].substitute(src_filebase=grader_filebase, src_filename=grader_filename).split()
  
  actual_type = task['division_metadata']['type']

  if actual_type == 'correct' or actual_type == 'sometimes':
    utils.progress('Testing team good input')
    if not run_test(grader_executer_cmd, team_correct, 100):
      return False

  if actual_type == 'wrong' or actual_type == 'sometimes':
    utils.progress('Testing team bad input')
    if not run_test(grader_executer_cmd, team_wrong, 200):
      return False
  
  return True

def grade(q, task, **kwargs):
  '''Grades a debug submission.'''
  
  correct = False
  metadata = {}
  
  try:
    payload = json.loads(task['payload'])
    team_select = payload['type']
    team_correct = payload['good']
    team_wrong = payload['bad']
    
    if task['division_metadata']['type'] == team_select:
      try:
        sandbox_dir = tempfile.mkdtemp(prefix='proco')
        os.chdir(sandbox_dir)
        correct = run_tests(task, team_select, team_correct, team_wrong)
        if correct:
          utils.progress('Correct')
        else:
          utils.progress('Wrong')
      finally:
        shutil.rmtree(sandbox_dir)
    else:
      utils.progress('Wrong (solution type mismatch)')
  except Exception, e:
    utils.progress('Internal error: ' + str(e))
  q.put({'correct' : correct, 'metadata' : metadata, 'division_id' : int(task['division_id']), 'team_id' : int(task['team_id']), 'problem_id' : int(task['problem_id'])})