<?php

namespace Maestro\Adapter\Amp\Job;

use Amp\Process\Process as AmpProcess;
use Amp\Promise;
use Maestro\Model\Console\ConsoleManager;
use Maestro\Model\Package\Workspace;

class ProcessHandler
{
    /**
     * @var Workspace
     */
    private $workspace;

    /**
     * @var ConsoleManager
     */
    private $consoleManager;

    public function __construct(Workspace $workspace, ConsoleManager $consoleManager)
    {
        $this->workspace = $workspace;
        $this->consoleManager = $consoleManager;
    }

    public function __invoke(Process $job): Promise
    {
        return \Amp\call(function (Process $job) {

            $process = new AmpProcess(
                $job->command(),
                $this->workspace->package($job->package())->path()
            );

            yield $process->start();

            $stdout = \Amp\call(function () use ($process, $job) {
                while (null !== $chunk = yield $process->getStdout()->read()) {
                    $this->consoleManager->stdout($job->package()->syncId())->write($chunk);
                }
            });

            $stderr = \Amp\call(function () use ($process, $job) {
                while (null !== $chunk = yield $process->getStderr()->read()) {
                    $this->consoleManager->stderr($job->package()->syncId())->write($chunk);
                }
            });

            yield [$stdout, $stderr];

            return yield $process->join();
        }, $job);
    }
}
