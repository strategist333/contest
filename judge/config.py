import json
import urllib2

handle_url = 'http://127.0.0.1/restricted/handle.php'

poll_interval = 3

def call(**kwargs):
  '''Helper function to POST a json call to config.handle_url.'''
  req = urllib2.urlopen(handle_url, json.dumps(kwargs, separators=(',',':')))
  if not req:
    raise Exception('Failed to open connection.')
  js = req.read()
  try:
    return json.loads(js)
  except Exception as e:
    print js
    raise e
