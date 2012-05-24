import os
import sys
import shutil
import tempfile

import utils
from utils import GradingException

def grade(q, task, verbose, callback):
  '''Sets up a file-based submission for grading, then calls a callback for grading.
  
  Sets up a sandbox directory, compiles the program, and calls the callback function for grading.'''

  correct = False
  metadata = {}

  try:
    sandbox_dir = tempfile.mkdtemp(prefix='proco')
    os.chdir(sandbox_dir)
    
    if verbose:
      utils.progress('Using temporary directory: %s' % sandbox_dir)

    payload = task['payload']
    team_filebase =  task['alias']
    team_extension = task['run_metadata']['extension']
    team_filename = team_filebase + '.' + team_extension
    utils.compile(payload, team_filebase, team_extension, team_filename)
    
    correct = callback(task, team_filebase, team_extension, team_filename, metadata, verbose)

  except GradingException, e:
    utils.progress(e.message)
    metadata['error'] = e.message
  except Exception, e:
    utils.progress('Internal error: ' + str(e))
    raise
  finally:
    if verbose:
      utils.progress('NOT removing temporary directory %s; please clean up manually!' % sandbox_dir)
    else:
      shutil.rmtree(sandbox_dir)

  q.put({'correct' : correct, 'metadata' : metadata, 'division_id' : int(task['division_id']), 'team_id' : int(task['team_id']), 'problem_id' : int(task['problem_id'])})