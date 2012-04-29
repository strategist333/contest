#!/usr/bin/python
import importlib
import json
import multiprocessing
import time

import config
import utils

class Judge:
  '''The entity that communicates with the server.'''
  
  def __init__(self):
    '''Initialize the auto grader.'''
    
    js = utils.call(action='initialize_judge')
    if not js['success']:
      raise Exception('Failed to initialize judge.');
    self.judge_id = int(js['judge_id'])
    self.contest_id = int(js['contest_id'])
    self.contest_type = js['contest_type']
    
  def fetch_task(self):
    '''Fetch a new grading task.'''
    
    js = utils.call(action='fetch_task', judge_id=self.judge_id, contest_id=self.contest_id)
    if not js['success']:
      raise Exception('Task not successfully fetched.');
    return js
    
  def submit_judgment(self, judgment_id, correct, metadata):
    '''Submits the result of the grading task.'''
    
    js = utils.call(action='submit_judgment', judgment_id=judgment_id, judge_id=self.judge_id, correct=correct, metadata=json.dumps(metadata, separators=(',',':')))
    if not js['success']:
      raise Exception('Judgment not successfully submitted.');
    return js
    
  def __str__(self):
    return 'judge_id %d, contest_id %d' % (self.judge_id, self.contest_id)

def _module_name(contest_type, problem_type):
  return 'modules.' + 'contest_' + contest_type + '.' + 'problem_' + problem_type;

def _import_module(contest_type, problem_type):
  modules = []
  modules.append(_module_name(contest_type, problem_type));
  modules.append(_module_name(contest_type, 'default'));
  modules.append(_module_name('default', problem_type));
  modules.append(_module_name('default', 'default'));
  for module in modules:
    try:
      return importlib.import_module(module)
    except Exception, e:
      print e
      pass
  raise Exception('No module found for %s.%s' % (contest_type, problem_type))

if __name__ == '__main__':
  judge = Judge()
  print time.strftime('[%H:%M:%S]:', time.localtime()),
  print 'Initialized judge to %s' % judge
  
  while True:
    print time.strftime('[%H:%M:%S]:', time.localtime()),
    task = judge.fetch_task()
    task_type = task['task_type']
    if task_type == 'grade':
      for key in ['run_metadata', 'problem_metadata', 'division_metadata']:
        task[key] = json.loads(task[key])
      print 'grading run_id %s (team %s, problem %s) of type %s... ' % (task['run_id'], task['team_username'], task['alias'], task['problem_type']),
      utils.reset_progress()
      module = _import_module(judge.contest_type, task['problem_type'])
      q = multiprocessing.Queue()
      grader = multiprocessing.Process(target=module.grade, args=(q, task,))
      grader.start()
      result = q.get()
      grader.join()
      print
      judge.submit_judgment(task['judgment_id'], **result)
    elif task_type == 'reset':
      judge = Judge()
      print 'reset judge to %s' % judge
    elif task_type == 'reset':
      judge = Judge()
      
    elif task_type == 'poll':
      print 'no tasks.'
      time.sleep(config.poll_interval)