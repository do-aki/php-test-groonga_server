<?php

namespace dooaki\Test\Test;

use dooaki\Test\GroongaServer;

class runTest extends \PHPUnit_Framework_TestCase {

    private $groonga;

    public function setUp()
    {
        $command = getenv('GROONGA_TEST_COMMAND');
        if (!$command) {
            $this->markTestSkipped("no GROONGA_TEST_COMMAND");
        }

        $this->groonga = new GroongaServer(array(
            'groonga' => $command,
        ));
    }

    public function test_run()
    {
        $this->groonga->run();

        $fp = stream_socket_client("tcp://127.0.0.1:{$this->groonga->getPort()}");
        $this->assertInternalType('resource', $fp);
        fclose($fp);
    }
}
