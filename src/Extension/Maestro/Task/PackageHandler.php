<?php

namespace Maestro\Extension\Maestro\Task;

use Amp\Promise;
use Amp\Success;
use Maestro\Node\Task;
use Maestro\Script\EnvVars;
use Maestro\Node\Environment;
use Maestro\Node\Exception\TaskFailed;
use Maestro\Node\TaskHandler;
use Maestro\Workspace\Workspace;
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

    public function execute(Task $package, Environment $environment): Promise
    {
        assert($package instanceof PackageTask);
        $workspace = $this->factory->createNamedWorkspace($package->name());

        if ($package->purgeWorkspace()) {
            $workspace->purge();
        }

        $this->createWorkspaceFolderIfNotExists($workspace);

        return new Success($environment->builder()->withParameters(array_merge($package->environment(), [
            'package' => $package,
            'workspace' => $workspace,
            'env' => EnvVars::create([
                'PACKAGE_WORKSPACE_PATH' => $workspace->absolutePath(),
                'PACKAGE_NAME' => $package->name()
            ])
        ]))->build());
    }

    private function createWorkspaceFolderIfNotExists(Workspace $workspace): void
    {
        if (file_exists($workspace->absolutePath())) {
            return;
        }

        if (@mkdir($workspace->absolutePath(), 0777, true)) {
            return;
        }

        throw new TaskFailed(sprintf(
            'Could not create folder "%s"',
            $workspace->absolutePath()
        ));
    }
}
