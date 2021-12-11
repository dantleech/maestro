<?php

namespace Maestro\Extension\Workspace;

use Maestro\Extension\Task\TaskExtension;
use Maestro\Extension\Workspace\Task\CwdWorkspaceHandler;
use Maestro\Extension\Workspace\Task\CwdWorkspaceTask;
use Maestro\Extension\Workspace\Task\MountedWorkspaceHandler;
use Maestro\Extension\Workspace\Task\MountedWorkspaceTask;
use Maestro\Extension\Workspace\Task\WorkspaceHandler;
use Maestro\Extension\Workspace\Task\WorkspaceTask;
use Maestro\Library\Workspace\PathStrategy\NestedDirectoryStrategy;
use Maestro\Library\Workspace\WorkspaceManager;
use Maestro\Library\Workspace\WorkspaceRegistry;
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
    const PARAM_WORKING_DIRECTORY = 'workspace.cwd';

    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        $container->register(WorkspaceManager::class, function (Container $container) {
            return new WorkspaceManager(
                new NestedDirectoryStrategy(),
                $container->get(WorkspaceRegistry::class),
                $container->getParameter(self::PARAM_WORKSPACE_NAMESPACE),
                $container->getParameter(self::PARAM_WORKSPACE_PATH)
            );
        });

        $container->register(WorkspaceRegistry::class, function (Container $container) {
            return new WorkspaceRegistry();
        });

        $container->register(MountedWorkspaceHandler::class, function (Container $container) {
            return new MountedWorkspaceHandler(
                $container->get(WorkspaceManager::class),
                $container->get(WorkspaceRegistry::class)
            );
        }, [
            TaskExtension::TAG_TASK_HANDLER => [
                'alias' => 'mountedWorkspace',
                'taskClass' => MountedWorkspaceTask::class
            ]
        ]);

        $container->register(WorkspaceHandler::class, function (Container $container) {
            return new WorkspaceHandler(
                $container->get(WorkspaceManager::class)
            );
        }, [
            TaskExtension::TAG_TASK_HANDLER => [
                'alias' => 'workspace',
                'taskClass' => WorkspaceTask::class
            ]
        ]);

        $container->register(CwdWorkspaceHandler::class, function (Container $container) {
            return new CwdWorkspaceHandler(
                $container->get(WorkspaceRegistry::class),
                $container->getParameter(self::PARAM_WORKING_DIRECTORY)
            );
        }, [
            TaskExtension::TAG_TASK_HANDLER => [
                'alias' => 'cwdWorkspace',
                'taskClass' => CwdWorkspaceTask::class
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
            self::PARAM_WORKSPACE_PATH,
            self::PARAM_WORKING_DIRECTORY
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
