import exceptions
import os
import signal
from string import Template
import subprocess
import sys
import shutil
import tempfile
import time
import traceback

import utils

def grade(q, task, callback, **kwargs):
  correct = False
  metadata = {}

  try:
    sandbox_dir = tempfile.mkdtemp(prefix='proco')
    os.chdir(sandbox_dir)

    team_select = task['run_metadata']['type']
    team_correct = task['run_metadata']['good']
    team_wrong = task['run_metadata']['bad']

    if task['division_metadata']['type'] == team_select:
      correct = callback(task, team_select, team_correct, team_wrong, metadata)
    else:
      utils.progress("Wrong (Solution Type mismatch)")

  except GradingException, e:
    utils.progress(e.message)
    metadata['error'] = e.message
  except Exception, e:
    utils.progress('Internal error: ' + str(e))
  finally:
    shutil.rmtree(sandbox_dir)

  q.put({'correct' : correct, 'metadata' : metadata, 'division_id' : int(task['division_id']), 'team_id' : int(task['team_id']), 'problem_id' : int(task['problem_id'])})
