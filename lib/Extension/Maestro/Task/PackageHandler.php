<?php

namespace Maestro\Extension\Maestro\Task;

use Amp\Promise;
use Amp\Success;
use Maestro\Script\EnvVars;
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

        if (!file_exists($workspace->absolutePath())) {
            mkdir($workspace->absolutePath(), 0777, true);
        }

        return new Success(Artifacts::create([
            'package' => $package,
            'workspace' => $workspace,
            'env' => EnvVars::create([
                'PACKAGE_WORKSPACE_PATH' => $workspace->absolutePath(),
                'PACKAGE_NAME' => $package->name()
            ])
        ]));
    }
}
