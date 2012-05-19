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
  # Compiles the judge cleanly and executes the test case on the judge

  time_limit = 5
  try:
    payload = task['problem_metadata']['grader']['src']
    grader_filebase = task['problem_metadata']['grader']['filebase']
    grader_extension = task['problem_metadata']['grader']['extension']
    grader_filename = grader_filebase + '.' + grader_extension
    modules.contest_debug.compile.compile(payload, grader_filebase, grader_extension, grader_filename)

    # Testing Correct Input
    #if(task['division_metadata']['type'] == "correct" || task['division_metadata']['type'] == "sometimes"):

    # Testing Wrong Input
    #if(task['division_metadata']['type'] == "wrong" || task['division_metadata']['type'] == "sometimes"):


  except Exception, e:
    utils.progress('Internal error when compiling grader.')
    raise Exception(e)

  return True


def grade(q, task, **kwargs):
  # Grades a debugging submission
  return modules.contest_debug.compile.grade(q, task, run_tests, **kwargs)
