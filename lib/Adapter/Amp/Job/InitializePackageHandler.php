<?php

namespace Maestro\Adapter\Amp\Job;

use Amp\Promise;
use Amp\Success;
use Maestro\Model\Package\Workspace;

final class InitializePackageHandler
{
    /**
     * @var Workspace
     */
    private $workspace;

    public function __construct(Workspace $workspace)
    {
        $this->workspace = $workspace;
    }

    public function __invoke(InitializePackage $initJob): Promise
    {
        $package = $initJob->packageDefinition();
        $workspace = $this->workspace->package($package);

        if ($initJob->purge()) {
            $workspace->remove();
        }

        $packagePath = $workspace->path();

        if (file_exists($packagePath)) {
            return new Success();
        }

        $jobs = [
            new Process($this->workspace->path(), sprintf('git clone %s %s', $package->url(), $packagePath), $package->consoleId())
        ];

        foreach ($initJob->packageDefinition()->initCommands() as $initCommand) {
            $jobs[] = new Process($packagePath, $initCommand, $package->consoleId());
        }

        foreach (array_reverse($jobs) as $job) {
            $initJob->queue()->prepend($job);
        }

        return new Success();
    }
}
