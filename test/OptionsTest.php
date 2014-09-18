<?php
namespace dooaki\Test\Test;

use dooaki\Test\GroongaServer;

class OptionsTest extends \PHPUnit_Framework_TestCase {

    private $bin;
    private $out;

    public function setUp()
    {
        $this->bin = __DIR__ . '/dummy_server';
        $this->out = $this->bin . '.out';
        if (!is_executable($this->bin)) {
            $this->markTestSkipped("cannot execute dummy_server");
        }
        if (file_exists($this->out)) {
            unlink($this->out);
        }
    }

    public function test_basic()
    {
        $g = new GroongaServer(
            array(
                'groonga' => $this->bin,
            )
        );
        $g->run();

        $args = json_decode(file_get_contents($this->out), true);
        $expect = array(
            'db' => $g->getDb(),
            '-d' => true,
            '--port' => (string)$g->getPort(),
            '--protocol' => 'http',
            '--log-path' => $g->getLogFile(),
            '-n' => true,
        );

        $this->assertSame($expect, array_intersect_assoc($expect, $args));
    }

    public function test_custom_log_name()
    {
        $g = new GroongaServer(
            array(
                'groonga' => $this->bin,
                'log-path' => 'my_log',
                'query-log-path' => 'my_query_log',
            )
        );
        $g->run();

        $args = json_decode(file_get_contents($this->out), true);

        $this->assertSame('my_log', basename($args['--log-path']));
        $this->assertSame('my_query_log', basename($args['--query-log-path']));
    }

    public function test_existing_db()
    {
        $f = tempnam(sys_get_temp_dir(), 'tgs');
        $g = new GroongaServer(
            array(
                'groonga' => $this->bin,
                'db'      => $f
            )
        );
        $g->run();

        $args = json_decode(file_get_contents($this->out), true);
        $this->assertFalse(isset($args['-n']));
        $this->assertSame(dirname($f), $g->getWorkDir());
    }
}

