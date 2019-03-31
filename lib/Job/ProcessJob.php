<?php

namespace Phpactor\Extension\Maestro\Job;

use Amp\Process\Process;
use Amp\Process\ProcessInputStream;
use Amp\Promise;
use Phpactor\Extension\Maestro\Job\Exception\ProcessFailed;
use Phpactor\Extension\Maestro\Model\Console;
use Phpactor\Extension\Maestro\Model\Job;

class ProcessJob implements Job
{
    private $command;
    private $cwd;
    private $console;

    public function __construct(Console $console, string $command, string $cwd)
    {
        $this->command = $command;
        $this->cwd = $cwd;
        $this->console = $console;
    }

    public function execute(): Promise
    {
        return \Amp\call(function () {
            $process = new Process($this->command, $this->cwd);

            $this->console->writeln(sprintf('Executing: %s in %s', $this->command, $this->cwd));

            yield $process->start();
            
            yield [
                $this->streamOutput('err', $process->getStderr()),
                $this->streamOutput('std', $process->getStdout()),
            ];

            $exitCode =  yield $process->join();

            if ($exitCode !== 0) {
                throw new ProcessFailed(sprintf(
                    'Process "%s" exited with non-zero exit code "%s"',
                    $this->command, $exitCode
                ));
            }

            return $exitCode;
        });
    }

    private function streamOutput(string $type, ProcessInputStream $stream)
    {
        return \Amp\call(function () use ($stream) {
            while (null !== $buffer = yield $stream->read()) {
                $this->console->write($buffer);
            }
        });
    }
}
