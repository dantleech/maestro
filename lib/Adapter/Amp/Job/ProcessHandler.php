<?php

namespace Maestro\Adapter\Amp\Job;

use Amp\Process\Process as AmpProcess;
use Amp\Promise;
use Maestro\Model\Console\ConsoleManager;
use Maestro\Model\Package\Workspace;

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
                while (null !== $chunk = yield $process->getStdout()->read()) {
                    $this->consoleManager->stdout($job->consoleId())->write($chunk);
                }
            });

            $stderr = \Amp\call(function () use ($process, $job) {
                while (null !== $chunk = yield $process->getStderr()->read()) {
                    $this->consoleManager->stderr($job->consoleId())->write($chunk);
                }
            });

            yield [$stdout, $stderr];

            return yield $process->join();
        }, $job);
    }
}
