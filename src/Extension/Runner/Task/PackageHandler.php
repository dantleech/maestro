<?php

namespace Maestro\Extension\Runner\Task;

use Amp\Promise;
use Amp\Success;
use Generator;
use Maestro\Extension\File\Task\PurgeDirectoryTask;
use Maestro\Extension\Vcs\Task\CheckoutTask;
use Maestro\Library\Support\Environment\Environment;
use Maestro\Library\Support\NodeMeta;
use Maestro\Library\Support\Package\Package;
use Maestro\Library\Artifact\Artifacts;
use Maestro\Library\Task\TaskRunner;
use Maestro\Library\Workspace\WorkspaceManager;
use function Amp\File\mkdir;

class PackageHandler
{
    /**
     * @var WorkspaceManager
     */
    private $workspaceManager;

    /**
     * @var bool
     */
    private $purge;


    public function __construct(WorkspaceManager $workspaceManager, bool $purge = false)
    {
        $this->workspaceManager = $workspaceManager;
        $this->purge = $purge;
    }

    public function __invoke(
        PackageTask $task,
        Environment $environment,
        TaskRunner $taskRunner,
        NodeMeta $nodeMeta
    ): Promise {
        return \Amp\call(function () use ($task, $environment, $taskRunner, $nodeMeta) {
            $name = $task->name() ?: $nodeMeta->name();
            $environment = $environment->spawnMerged([
                'PACKAGE_NAME' => $name
            ]);
            return new Success([
                $environment,
                new Package(
                    $name,
                    $task->version()
                ),
                yield from $this->createWorkspace($taskRunner, $task, $environment, $name)
            ]);
        });
    }

    private function createWorkspace(
        TaskRunner $taskRunner,
        PackageTask $task,
        Environment $environment,
        string $name
    ): Generator {
        $workspace = $this->workspaceManager->createNamedWorkspace($name);

        if ($this->purge || $task->purgeWorkspace()) {
            yield $taskRunner->run(new PurgeDirectoryTask($workspace->absolutePath()), new Artifacts());
        }

        if (!file_exists($workspace->absolutePath())) {
            // if we don't yield, then the directory doesn't get created
            yield mkdir($workspace->absolutePath(), 0777, true);
        }

        $url = $task->url();
        if ($url) {
            yield $taskRunner->run(new CheckoutTask($url), new Artifacts([
                $workspace, $environment
            ]));
        }

        return $workspace;
    }
}
