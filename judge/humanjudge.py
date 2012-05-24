#!/usr/bin/python
import json
import multiprocessing
import time
import sys

import config
import utils
    
if __name__ == '__main__':
  utils.init()
  if len(sys.argv) != 3:
    print 'Usage: %s <team_username> <problem_alias>' % sys.argv[0]
    sys.exit(1)
  
  team_username = sys.argv[1]
  problem_alias = sys.argv[2]
  
  utils.reset_progress(True)
    
  task = utils.call(action='fetch_run', team_username=team_username, problem_alias=problem_alias)
  if not task['success']:
    raise Exception('Failed to fetch run.')
  
  print 'Grading run_id %s (team %s, problem %s) of type %s... ' % (task['run_id'], task['team_username'], task['alias'], task['problem_type']),
  
  module = utils.import_module(task['contest_type'], task['problem_type'])
  
  for key in ['run_metadata', 'problem_metadata', 'division_metadata']:
    task[key] = json.loads(task[key])
  
  q = multiprocessing.Queue()
  grader = multiprocessing.Process(target=module.grade, args=(q, task, True))
  grader.start()
  result = q.get()
  grader.join()
  
  print 'Final judgment: %s' % ('CORRECT' if result['correct'] else 'INCORRECT')
