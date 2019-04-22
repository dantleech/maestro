<?php

namespace Maestro\Adapter\Amp\Job;

use Amp\Process\Process as AmpProcess;
use Amp\Promise;
use Maestro\Adapter\Amp\Job\Exception\ProcessNonZeroExitCode;
use Maestro\Model\Console\ConsoleManager;
use Maestro\Model\Package\Workspace;
use RuntimeException;

class ProcessHandler
{
    /**
     * @var ConsoleManager
     */
    private $consoleManager;

    public function __construct(ConsoleManager $consoleManager)
    {
        $this->consoleManager = $consoleManager;
    }

    public function __invoke(Process $job): Promise
    {
        return \Amp\call(function (Process $job) {

            $this->consoleManager->stdout($job->consoleId())->writeln('EXEC: ' . $job->command());
            $process = new AmpProcess(
                $job->command(),
                $job->workingDirectory()
            );

            yield $process->start();

            $stdout = \Amp\call(function () use ($process, $job) {
                $lastChunk = null;
                while (null !== $chunk = yield $process->getStdout()->read()) {
                    $this->consoleManager->stdout($job->consoleId())->write($chunk);
                    $lastChunk = $chunk;
                }
                return $lastChunk;
            });

            $stderr = \Amp\call(function () use ($process, $job) {
                $lastChunk = null;
                while (null !== $chunk = yield $process->getStderr()->read()) {
                    $this->consoleManager->stderr($job->consoleId())->write($chunk);
                    $lastChunk = $chunk;
                }
                return $lastChunk;
            });

            $outs = yield [$stdout, $stderr];

            $exitCode = yield $process->join();

            if ($exitCode !== 0) {
                throw new ProcessNonZeroExitCode(sprintf(
                    '%s%s', $outs[0], $outs[1]
                ), $exitCode);
            }

            return $outs[0];
        }, $job);
    }
}
