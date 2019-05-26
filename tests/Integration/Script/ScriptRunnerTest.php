<?php

namespace Maestro\Tests\Integration\Script;

use Maestro\Script\ScriptResult;
use Maestro\Script\ScriptRunner;
use Maestro\Tests\IntegrationTestCase;
use Psr\Log\LoggerInterface;

class ScriptRunnerTest extends IntegrationTestCase
{
    /**
     * @var ObjectProphecy|LoggerInterface
     */
    private $logger;

    /**
     * @var ScriptRunner
     */
    private $runner;


    protected function setUp(): void
    {
        $this->workspace()->reset();
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->runner = new ScriptRunner($this->logger->reveal());
    }

    public function testRunsCommand()
    {
        $result = \Amp\Promise\wait($this->runner->run('echo Hello', $this->workspace()->path('/'), []));
        $this->assertInstanceOf(ScriptResult::class, $result);
        $this->assertEquals(0, $result->exitCode());
        $this->assertEquals('Hello', $result->lastStdout());
    }

    public function testRunsACommandThatFails()
    {
        $result = \Amp\Promise\wait($this->runner->run('exit 5', $this->workspace()->path('/'), []));
        $this->assertInstanceOf(ScriptResult::class, $result);
        $this->assertEquals(5, $result->exitCode());
    }
}
