<?php

namespace Maestro\Adapter\Amp\Job;

use Amp\Process\Process as AmpProcess;
use Amp\Promise;
use Maestro\Model\Package\Workspace;

class ProcessHandler
{
    /**
     * @var Workspace
     */
    private $workspace;

    public function __construct(Workspace $workspace)
    {
        $this->workspace = $workspace;
    }

    public function __invoke(Process $process): Promise
    {
        return \Amp\call(function (Process $process) {

            $process = new AmpProcess(
                $process->command(),
                $this->workspace->package($process->package())->path()
            );

            $pid = yield $process->getPid();

            yield $process->join();

        }, $process);
    }
}
