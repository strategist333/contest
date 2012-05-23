import utils
from utils import GradingException

languages = {
  'c'    : dict(compiler=Template('gcc -o $src_filebase $src_filename'),
                check_for=Template('$src_filebase'),
                compile_time_limit=10),
  'cc'   : dict(compiler=Template('g++ -o $src_filebase $src_filename'),
                check_for=Template('$src_filebase'),
                compile_time_limit=10),
  'cpp'  : dict(compiler=Template('g++ -o $src_filebase $src_filename'),
                check_for=Template('$src_filebase'),
                compile_time_limit=10),
  'java' : dict(compiler=Template('javac -cp . $src_filename'),
                check_for=Template('$src_filebase.class'),
                compile_time_limit=10),
  'py'   : dict(compiler=Template('python -mpy_compile $src_filename'),
                check_for=Template('$src_filebase.pyc'),
                compile_time_limit=10),
}

def compile(payload, src_filebase, src_extension, src_filename):
  '''Compile a payload in the current working directory.'''

  with open(src_filename, 'w+') as src_file:
    src_file.write(payload)
    src_file.flush()
    src_file.close()

    utils.progress('Compiling ' + src_filename)

    compiler_cmd = languages[src_extension]['compiler'].substitute(src_filebase=src_filebase, src_filename=src_filename)
    if compiler_cmd is not None:
      compiler_cmd = compiler_cmd.split()
      time_limit = languages[src_extension]['compile_time_limit']
      compiler = subprocess.Popen(compiler_cmd, stdout=open(os.devnull, 'w'), stderr=subprocess.STDOUT, preexec_fn=os.setsid)
      start_time = time.time()
      while compiler.poll() is None and (time.time() - start_time < time_limit):
        time.sleep(0.5)
      if compiler.poll() is None:
        os.killpg(compiler.pid, signal.SIGKILL)
        raise GradingException('Compile time limit exceeded')
      if compiler.returncode != 0:
        raise GradingException('Compiler error')

    check_for = languages[src_extension]['check_for'].substitute(src_filebase=src_filebase, src_filename=src_filename)

    if not os.path.exists(check_for):
      raise GradingException('Compiler error')
