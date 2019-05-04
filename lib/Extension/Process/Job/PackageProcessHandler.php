<?php

namespace Maestro\Extension\Process\Job;

use Amp\Promise;
use Amp\Success;
use Maestro\Model\Package\Workspace;

class PackageProcessHandler
{
    /**
     * @var Workspace
     */
    private $workspace;

    public function __construct(Workspace $workspace)
    {
        $this->workspace = $workspace;
    }

    public function __invoke(PackageProcess $process): Promise
    {
        $process->queue()->enqueue(
            new Process(
                $this->workspace->package($process->packageDefinition())->path(),
                $process->command(),
                $process->packageDefinition()->ttyId()
            )
        );

        return new Success();
    }
}
