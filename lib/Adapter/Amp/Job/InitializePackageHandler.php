<?php

namespace Maestro\Adapter\Amp\Job;

use Amp\Promise;
use Amp\Success;
use Maestro\Model\Package\Workspace;

class InitializePackageHandler
{
    /**
     * @var Workspace
     */
    private $workspace;

    public function __construct(Workspace $workspace)
    {
        $this->workspace = $workspace;
    }

    public function __invoke(InitializePackage $job): Promise
    {
        $package = $job->packageDefinition();
        $packagePath = $this->workspace->package($package)->path();

        if (file_exists($packagePath)) {
            return new Success();
        }

        $job->queue()->prepend(
            new Process($this->workspace->path(), sprintf('git clone %s %s', $package->repoUrl(), $packagePath), $package->consoleId())
        );

        return new Success();
    }
}
