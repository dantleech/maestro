<?php

namespace Maestro\Extension\Maestro\Task;

use Amp\Promise;
use Amp\Success;
use Maestro\Graph\Task;
use Maestro\Graph\Environment;
use Maestro\Graph\Exception\TaskFailed;
use Maestro\Graph\TaskHandler;
use Maestro\Package\Package;
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
        $builder = $environment->builder();
        $builder->withWorkspace($workspace);
        $builder->mergeEnv(array_merge($package->env(), [
            'PACKAGE_WORKSPACE_PATH' => $workspace->absolutePath(),
            'PACKAGE_NAME' => $package->name()
        ]));
        $builder->withVars(array_merge([
            'package' => new Package(
                $package->name(),
                $package->version()
            )
        ], $package->vars()));

        return new Success($builder->build());
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
