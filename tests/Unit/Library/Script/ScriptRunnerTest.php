<?php

namespace Maestro\Tests\Unit\Library\Script;

use Maestro\Library\Script\ScriptResult;
use Maestro\Library\Script\ScriptRunner;
use Maestro\Tests\IntegrationTestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;

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
        $this->runner = new ScriptRunner($this->logger->reveal(), $this->workspace()->path('/'));
    }

    public function testRunsCommand()
    {
        $result = \Amp\Promise\wait($this->runner->run('echo Hello', $this->workspace()->path('/'), []));
        $this->assertInstanceOf(ScriptResult::class, $result);
        $this->assertEquals(0, $result->exitCode());
        $this->assertStringContainsString('Hello', $result->stdout());
    }

    public function testRunsCommandInDefaultWorkingDirectory()
    {
        $result = \Amp\Promise\wait($this->runner->run('echo Hello', null, []));
        $this->assertInstanceOf(ScriptResult::class, $result);
        $this->assertEquals(0, $result->exitCode());
        $this->assertStringContainsString('Hello', $result->stdout());
    }


    public function testRunsACommandThatFails()
    {
        $result = \Amp\Promise\wait($this->runner->run('exit 5', $this->workspace()->path('/'), []));
        $this->assertInstanceOf(ScriptResult::class, $result);
        $this->assertEquals(5, $result->exitCode());
    }

    public function testThrowsExceptionIfWorkingDirectoryDoesNotExist()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Working directory ');
        \Amp\Promise\wait($this->runner->run('exit 5', $this->workspace()->path('/not-existing'), []));
    }
}
