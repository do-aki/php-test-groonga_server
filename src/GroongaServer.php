<?php
namespace dooaki\Test;

use dooaki\Net\EmptyPort;

class GroongaServer {

    /** @var array  */
    private $conf;

    /** @var string groonga command */
    private $command;

    /** @var string working directory (include db file) */
    private $workdir;

    /** @var boolean cleanup at destructor */
    private $need_clean_up = false;

    /** @var integer groonga server process id  */
    private $pid = null;

    /** @var array overwritable options */
    private $options = array(
        'encoding',
        'log-level',
        'bind-address',
        'port',
        'server-id',
        'document-root',
        'protocol',
        'log-path',
        'query-log-path',
        'max-threads',
        'pid-path',
        'config-path',
        'cache-limit',
        'default-match-escalation-threshold',
    );

    public function __construct(array $conf = array())
    {
        $conf += array(
            'db'             => null,
            'tempdir'        => sys_get_temp_dir(),
            'port'           => null,
            'protocol'       => 'http',
            'log-path'       => 'log',
            'query-log-path' => null,
            'pid-path'       => 'pid'
        );

        if (isset($conf['groonga'])) {
            $prog = $conf['groonga'];
        } else {
            $prog = trim(`which groonga`);
        }
        if (!is_executable($prog)) {
            throw new \UnexpectedValueException("{$prog} is not executable");
        }
        $this->command = $prog;

        if (isset($conf['db'])) {
            $this->workdir = dirname($conf['db']);
        } else {
            $this->workdir = $this->makeWorkDir($conf['tempdir']);
            $this->need_clean_up = true;
        }

        $this->conf = $conf;
    }

    public function __destruct()
    {
        if ($this->pid) {
            $this->shutdownProcess($this->pid);
        }

        if ($this->need_clean_up) {
            $this->cleanUp($this->workdir);
        }
    }

    public function getPid()
    {
        return $this->pid;
    }

    public function getWorkDir()
    {
        return $this->workdir;
    }

    public function getPort()
    {
        return intval($this->conf['port']);
    }

    public function getDb()
    {
        return $this->conf['db'];
    }

    public function getLogFile()
    {
        return $this->conf['log-path'];
    }

    public function getQueryLogFile()
    {
        return $this->conf['query-log-path'];
    }

    public function run()
    {
        if ($this->conf['pid-path'] && $this->conf['pid-path'][0] !== '/') {
            $this->conf['pid-path'] = "{$this->workdir}/{$this->conf['pid-path']}";
        }

        if ($this->conf['log-path'] && $this->conf['log-path'][0] !== '/') {
            $this->conf['log-path'] = "{$this->workdir}/{$this->conf['log-path']}";
        }

        if ($this->conf['query-log-path'] && $this->conf['log-path'][0] !== '/') {
            $this->conf['query-log-path'] = "{$this->workdir}/{$this->conf['query-log-path']}";
        }

        if ($this->conf['port'] === null) {
            $this->conf['port'] = EmptyPort::find();
        }

        $options = '-d ';
        foreach ($this->options as $opt) {
            if (isset($this->conf[$opt])) {
                $options .= " --{$opt} " . escapeshellarg($this->conf[$opt]);
            }
        }

        if ($this->conf['db'] === null) {
            $this->conf['db'] = "{$this->workdir}/db";
        }
        $dest = (file_exists($this->conf['db'])?'':'-n ') . escapeshellarg($this->conf['db']);

        if (file_exists($this->conf['pid-path'])) {
            unlink($this->conf['pid-path']);
        }
        exec("{$this->command} {$options} {$dest} > /dev/null 2>&1 &");

        if (!EmptyPort::wait($this->conf['port'], 5)) {
            throw new \RuntimeException("port {$this->conf['port']} is not open.");
        }

        $pid = intval(@file_get_contents($this->conf['pid-path']));
        if (!$pid) {
            throw new \RuntimeException("can not get pid: {$this->conf['pid-path']} was not create?");
        }

        $this->pid = $pid;
    }

    private function makeWorkDir($tempdir)
    {
        $tmpfile = tempnam($tempdir, 'dtgs');
        if (!$tmpfile) {
            throw new \RuntimeException("cannot create temporary file in {$tempdir}");
        }
        @unlink($tmpfile);
        if (!mkdir($tmpfile)) {
            throw new \RuntimeException("cannot create working directory");
        }

        return $tmpfile;
    }

    private function shutdownProcess($pid)
    {
        $wait = 10000;
        $try  = 300; // 3sec

        posix_kill($pid, SIGTERM);
        while (posix_kill($pid, 0) && 0 < $try--) {
            usleep($wait);
        }

        if ($try < 0) {
            throw new \RuntimeException("fail shutdown process. pid:{$pid}");
        }
    }

    private function cleanUp($dir)
    {
        return ;
        $clean_up = function($dir) use (&$clean_up) {
            $d = dir($dir);
            if (!$d) {
                throw new \RuntimeException("cannot open dir {$dir}");
            }

            while(false !== ($n = $d->read())) {
                if ($n === '.' || $n === '..') {
                  continue;
                }

                $f = "{$dir}/{$n}";
                if (is_dir($f)) {
                    $clean_up($f);
                } else {
                    unlink($f);
                }
            }

            $d->close();
            rmdir($dir);
        };
        $clean_up($dir);
    }

}

