import json
import os
import shutil
import signal
from string import Template
import subprocess
import time
import tempfile

import utils

def _run_test(grader_executer_cmd, team_input, desired_return_code, verbose):
  '''Run an individual test and return whether it was accepted by the grader.'''
  time_limit = 5

  stdin = tempfile.TemporaryFile(bufsize=10485760)
  stdin.write(team_input)
  stdin.flush()
  stdin.seek(0)
  
  if verbose:
    print 'Team input'
    print '----'
    print team_input
    print '----'
  
  grader_executer = subprocess.Popen(grader_executer_cmd, stdin=stdin, stdout=open(os.devnull, 'w'), stderr=open(os.devnull, 'w'), preexec_fn=os.setsid, close_fds=True)    
  start_time = time.time()
  while grader_executer.poll() is None and time.time() - start_time <= time_limit:
    time.sleep(0.5)
  grader_finished = grader_executer.poll() is not None
  if grader_executer.poll() is None:
    if verbose:
      utils.progress('Grader executable did not finish; killing PID %d' % grader_executer.pid)
    os.killpg(grader_executer.pid, signal.SIGKILL)
    return False
  elif grader_executer.returncode != desired_return_code:
    if verbose:
      utils.progress('Grader executable returned %d, not desired %d' % (grader_executer.returncode, desired_return_code))
    return False
  else:
    return True

def _run_tests(task, team_select, team_correct, team_wrong, verbose):
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
    if not _run_test(grader_executer_cmd, team_correct, 100, verbose):
      return False

  if actual_type == 'wrong' or actual_type == 'sometimes':
    utils.progress('Testing team bad input')
    if not _run_test(grader_executer_cmd, team_wrong, 200, verbose):
      return False
  
  return True

def grade(q, task, verbose):
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
        
        if verbose:
          utils.progress('Using temporary directory: %s' % sandbox_dir)

        correct = _run_tests(task, team_select, team_correct, team_wrong, verbose)
        if correct:
          utils.progress('Correct')
        else:
          utils.progress('Wrong')
      finally:        
        if verbose:
          utils.progress('NOT removing temporary directory %s; please clean up manually!' % sandbox_dir)
        else:
          shutil.rmtree(sandbox_dir)
    else:
      if verbose:
        utils.progress('Team selected %s, should be %s', team_select, task['division_metadata']['type'])
      utils.progress('Wrong (solution type mismatch)')
  except Exception, e:
    utils.progress('Internal error: ' + str(e))
    raise
  q.put({'correct' : correct, 'metadata' : metadata, 'division_id' : int(task['division_id']), 'team_id' : int(task['team_id']), 'problem_id' : int(task['problem_id'])})