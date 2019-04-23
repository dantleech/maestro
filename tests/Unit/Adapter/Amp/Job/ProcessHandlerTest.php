<?php

namespace Maestro\Tests\Unit\Adapter\Amp\Job;

use Maestro\Adapter\Amp\Job\Exception\ProcessNonZeroExitCode;
use Maestro\Adapter\Amp\Job\Process;
use Maestro\Adapter\Amp\Job\ProcessHandler;
use Maestro\Model\Console\Console;
use Maestro\Model\Console\ConsoleManager;
use Maestro\Model\Job\Test\HandlerTester;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class ProcessHandlerTest extends TestCase
{
    /**
     * @var ObjectProphecy
     */
    private $consoleManger;
    /**
     * @var ObjectProphecy
     */
    private $stdout;
    /**
     * @var ObjectProphecy
     */
    private $stderr;

    protected function setUp(): void
    {
        $this->consoleManger = $this->prophesize(ConsoleManager::class);
        $this->stdout = $this->prophesize(Console::class);
        $this->stderr = $this->prophesize(Console::class);

        $this->consoleManger->stderr(Argument::any())->willReturn($this->stderr->reveal());
        $this->consoleManger->stdout(Argument::any())->willReturn($this->stdout->reveal());
    }

    public function testDispatchesCommand()
    {
        $this->stdout->writeln(Argument::containingString('EXEC: echo Hello'))->shouldBeCalled();
        $this->stdout->write(Argument::containingString('Hello'))->shouldBeCalled();

        $lastLine = HandlerTester::create()->dispatch(
            new Process(__DIR__, 'echo Hello', 'foo'),
            new ProcessHandler($this->consoleManger->reveal())
        );
        self::assertEquals('Hello', $lastLine, 'Returned last line');
    }

    public function testThrowsExceptionOnNonZeroExitCode()
    {
        $this->expectException(ProcessNonZeroExitCode::class);
        $this->stdout->writeln(Argument::containingString('EXEC: thisisnotacommand'))->shouldBeCalled();

        HandlerTester::create()->dispatch(
            new Process(__DIR__, 'thisisnotacommand', 'foo'),
            new ProcessHandler($this->consoleManger->reveal())
        );
    }
}
