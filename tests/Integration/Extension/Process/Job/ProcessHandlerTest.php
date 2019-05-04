<?php

namespace Maestro\Tests\Integration\Amp\Job;

use Maestro\Extension\Process\Job\Exception\ProcessNonZeroExitCode;
use Maestro\Extension\Process\Job\Process;
use Maestro\Extension\Process\Job\ProcessHandler;
use Maestro\Model\Tty\Tty;
use Maestro\Model\Tty\TtyManager;
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
        $this->consoleManger = $this->prophesize(TtyManager::class);
        $this->stdout = $this->prophesize(Tty::class);
        $this->stderr = $this->prophesize(Tty::class);

        $this->consoleManger->stderr(Argument::any())->willReturn($this->stderr->reveal());
        $this->consoleManger->stdout(Argument::any())->willReturn($this->stdout->reveal());
    }

    public function testDispatchesCommand()
    {
        $this->stdout->writeln(Argument::containingString('# echo Hello'))->shouldBeCalled();
        $this->stdout->writeln(Argument::containingString('Hello'))->shouldBeCalled();

        $lastLine = HandlerTester::create(
            new ProcessHandler($this->consoleManger->reveal())
        )->dispatch(
            Process::class,
            [
                'workingDirectory' => __DIR__,
                'command' => 'echo Hello',
                'ttyId' => 'foo',
            ]
        );
        self::assertEquals('Hello', $lastLine, 'Returned last line');
    }

    public function testThrowsExceptionOnNonZeroExitCode()
    {
        $this->expectException(ProcessNonZeroExitCode::class);
        $this->stdout->writeln(Argument::containingString('# thisisnotacommand'))->shouldBeCalled();

        HandlerTester::create(
            new ProcessHandler($this->consoleManger->reveal())
        )->dispatch(
            Process::class,
            [
                'workingDirectory' => __DIR__,
                'command' => 'thisisnotacommand',
                'ttyId' => 'foo',
            ]
        );
    }
}
