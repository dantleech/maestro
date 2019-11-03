<?php

namespace Maestro\Extension\Workspace;

use Maestro\Extension\Task\TaskExtension;
use Maestro\Extension\Workspace\Task\MountedWorkspaceHandler;
use Maestro\Extension\Workspace\Task\MountedWorkspaceTask;
use Maestro\Library\Workspace\PathStrategy\NestedDirectoryStrategy;
use Maestro\Library\Workspace\WorkspaceManager;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\MapResolver\Resolver;
use Webmozart\PathUtil\Path;
use function Safe\getcwd;

class WorkspaceExtension implements Extension
{
    const PARAM_WORKSPACE_PATH = 'workspace.path';
    const PARAM_WORKSPACE_NAMESPACE = 'workspace.namespace';


    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        $container->register(WorkspaceManager::class, function (Container $container) {
            return new WorkspaceManager(
                new NestedDirectoryStrategy(),
                $container->getParameter(self::PARAM_WORKSPACE_NAMESPACE),
                $container->getParameter(self::PARAM_WORKSPACE_PATH)
            );
        });

        $container->register(MountedWorkspaceHandler::class, function (Container $container) {
            return new MountedWorkspaceHandler(
                $container->get(WorkspaceManager::class)
            );
        }, [
            TaskExtension::TAG_TASK_HANDLER => [
                'alias' => 'mountedWorkspace',
                'taskClass' => MountedWorkspaceTask::class
            ]
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema)
    {
        $schema->setDefaults([
            self::PARAM_WORKSPACE_NAMESPACE => '',
        ]);
        $schema->setRequired([
            self::PARAM_WORKSPACE_PATH
        ]);
        $schema->setCallback(self::PARAM_WORKSPACE_PATH, function ($config) {
            $path = $config[self::PARAM_WORKSPACE_PATH];
            if (Path::isAbsolute($path)) {
                return $path;
            }

            return Path::join([getcwd(), $path]);
        });
    }
}
