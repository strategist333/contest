#!/usr/bin/python
import json
import multiprocessing
import time
import sys

import config
import utils

class AutoJudge:
  '''The entity that communicates with the server.'''
  
  def __init__(self):
    '''Initialize the auto grader.'''
    
    js = utils.call(action='initialize_judge')
    if not js['success']:
      raise Exception('Failed to initialize judge.');
    self.judge_id = int(js['judge_id'])
    self.contest_id = int(js['contest_id'])
    self.contest_type = js['contest_type']
    self.cache = {}
    
  def fetch_task(self):
    '''Fetch a new grading task.'''
    
    js = utils.call(action='fetch_task', judge_id=self.judge_id, contest_id=self.contest_id)
    if not js['success']:
      raise Exception('Task not successfully fetched.')
    return js
  
  def submit_judgment(self, judgment_id, correct, metadata, **kwargs):
    '''Submits the result of the grading task.'''
    
    js = utils.call(action='submit_judgment', judgment_id=judgment_id, judge_id=self.judge_id, contest_id=self.contest_id, correct=correct, metadata=json.dumps(metadata, separators=(',',':')), **kwargs)
    if not js['success']:
      raise Exception('Judgment not successfully submitted.')
    return js
    
  def get_cached_metadata(self, problem_id, division_id, problem_metadata_hash, division_metadata_hash):
    '''Fetches cached metadata based on hash.'''
    
    problem_metadata = None
    division_metadata = None
    if problem_id in self.cache and self.cache[problem_id]['problem_metadata_hash'] == problem_metadata_hash:
      problem_metadata = self.cache[problem_id]['problem_metadata']
    if (problem_id in self.cache and
        division_id in self.cache[problem_id]['division_cache'] and
        self.cache[problem_id]['division_cache'][division_id]['division_metadata_hash'] == division_metadata_hash):
      division_metadata = self.cache[problem_id]['division_cache'][division_id]['division_metadata']   
    return (problem_metadata, division_metadata)
    
  def update_cached_metadata(self, problem_id, division_id, problem_metadata_hash, division_metadata_hash):
    '''Updates cached metadata and checks that the latest values are the hashed values.'''
    
    js = utils.call(action='fetch_task_metadata', problem_id=problem_id, division_id=division_id, contest_id=self.contest_id)
    if not js['success']:
      raise Exception('Metadata not successfully fetched.')
    
    if js['problem_metadata_hash'] != problem_metadata_hash:
      raise Exception('Problem metadata mismatch: got %s, expected %s' % (js['problem_metadata_hash'], problem_metadata_hash))
    if js['division_metadata_hash'] != division_metadata_hash:
      raise Exception('Division metadata mismatch: got %s, expected %s' % (js['division_metadata_hash'], division_metadata_hash))
    
    problem_metadata = json.loads(js['problem_metadata'])
    division_metadata = json.loads(js['division_metadata'])
    
    if problem_id not in self.cache:
      self.cache[problem_id] = {'division_cache' : {}}
    self.cache[problem_id]['problem_metadata'] = problem_metadata
    self.cache[problem_id]['problem_metadata_hash'] = problem_metadata_hash
    if division_id not in self.cache[problem_id]['division_cache']:
      self.cache[problem_id]['division_cache'][division_id] = {}
    self.cache[problem_id]['division_cache'][division_id]['division_metadata'] = division_metadata
    self.cache[problem_id]['division_cache'][division_id]['division_metadata_hash'] = division_metadata_hash
    return (problem_metadata, division_metadata)
    
  def __str__(self):
    return 'judge_id %d, contest_id %d' % (self.judge_id, self.contest_id)

if __name__ == '__main__':
  utils.init()
  judge = AutoJudge()
  print time.strftime('[%H:%M:%S]:', time.localtime()),
  print 'Initialized judge to %s' % judge
  
  while True:
    print time.strftime('[%H:%M:%S]:', time.localtime()),
    task = judge.fetch_task()
    task_type = task['task_type']
    if task_type == 'grade':
      task['run_metadata'] = json.loads(task['run_metadata'])
      print 'Grading run_id %s (team %s, problem %s) of type %s... ' % (task['run_id'], task['team_username'], task['alias'], task['problem_type']),
      utils.reset_progress(False)
      
      problem_metadata, division_metadata = judge.get_cached_metadata(task['problem_id'], task['division_id'], task['problem_metadata_hash'], task['division_metadata_hash'])
      if problem_metadata is None or division_metadata is None:
        utils.progress('Refreshing metadata')
        problem_metadata, division_metadata = judge.update_cached_metadata(task['problem_id'], task['division_id'], task['problem_metadata_hash'], task['division_metadata_hash'])
      
      task['problem_metadata'] = problem_metadata
      task['division_metadata'] = division_metadata
      
      module = utils.import_module(judge.contest_type, task['problem_type'])
      q = multiprocessing.Queue()
      grader = multiprocessing.Process(target=module.grade, args=(q, task, False))
      grader.start()
      result = q.get()
      grader.join()
      print
      judge.submit_judgment(judgment_id=int(task['judgment_id']), **result)
    elif task_type == 'reset':
      judge = AutoJudge()
      print 'Reset judge to %s' % judge
    elif task_type == 'halt':
      print 'Shutting down'
      break
    elif task_type == 'poll':
      print 'Waiting for task...',
      sys.stdout.write('\r')
      sys.stdout.flush()
      time.sleep(config.poll_interval)
