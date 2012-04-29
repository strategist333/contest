#!/usr/bin/python
import json
import urllib2
import sys
import tempfile

import config

def call(**kwargs):
  '''Helper function to POST a json call to config.handle_url.'''
  
  req = urllib2.urlopen(config.handle_url, json.dumps(kwargs, separators=(',',':')))
  if not req:
    raise Exception('Failed to open connection.')
  js = req.read()
  try:
    return json.loads(js)
  except Exception as e:
    print js
    raise e

def setup_tmpdir():
  '''Makes a temporary directory for sandboxing.'''
  
  return tempfile.mkdtemp(prefix='proco')
  

def progress(s):
  '''Outputs a progress indication.'''
  
  sys.stdout.write('\b' * progress.last_len)
  sys.stdout.write(s)
  sys.stdout.flush()
  progress.last_len = len(s)
  
def reset_progress():
  progress.last_len = 0