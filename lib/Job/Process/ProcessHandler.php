<?php

namespace Phpactor\Extension\Maestro\Job\Process;

use Amp\Process\Process;
use Amp\Process\ProcessInputStream;
use Amp\Promise;
use Phpactor\Extension\Maestro\Job\Process\Exception\ProcessFailed;
use Phpactor\Extension\Maestro\Model\Console\Console;
use Phpactor\Extension\Maestro\Model\Console\ConsolePool;
use Phpactor\Extension\Maestro\Model\Job\JobHandler;

class ProcessHandler implements JobHandler
{
    /**
     * @var ConsolePool
     */
    private $consoles;

    public function __construct(ConsolePool $consoles)
    {
        $this->consoles = $consoles;
    }

    public function __invoke(ProcessJob $job): Promise
    {
        return \Amp\call(function () use ($job) {
            $console = $this->consoles->get($job->console());

            $process = new Process($job->command(), $job->cwd());
            $console->writeln(sprintf('Executing: %s in %s', $job->command(), $job->cwd()));

            yield $process->start();
            
            yield [
                $this->streamOutput($console, 'err', $process->getStderr()),
                $this->streamOutput($console, 'std', $process->getStdout()),
            ];

            $exitCode =  yield $process->join();

            if ($exitCode !== 0) {
                throw new ProcessFailed(sprintf(
                    'Process "%s" exited with non-zero exit code "%s"',
                    $job->command(), $exitCode
                ));
            }

            return $exitCode;
        });
    }

    private function streamOutput(Console $console, string $type, ProcessInputStream $stream)
    {
        return \Amp\call(function () use ($stream, $console) {
            while (null !== $buffer = yield $stream->read()) {
                $console->write($buffer);
            }
        });
    }
}
