<?php

namespace Maestro\Extension\Maestro\Task;

use Amp\Promise;
use Amp\Success;
use Maestro\Task\Artifacts;
use Maestro\Task\TaskHandler;
use Maestro\Task\Task\PackageTask;
use Maestro\Workspace\WorkspaceFactory;

class PackageHandler implements TaskHandler
{
    /**
     * @var WorkspaceFactory
     */
    private $factory;

    public function __construct(WorkspaceFactory $factory)
    {
        $this->factory = $factory;
    }

    public function __invoke(PackageTask $package): Promise
    {
        $workspace = $this->factory->createNamedWorkspace($package->name());

        return new Success(Artifacts::create([
            'package' => $package,
            'workspace' => $workspace,
        ]));
    }
}
