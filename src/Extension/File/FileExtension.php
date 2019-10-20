<?php

namespace Maestro\Extension\File;

use Maestro\Extension\File\Task\PurgeDirectoryHandler;
use Maestro\Extension\File\Task\PurgeDirectoryTask;
use Maestro\Extension\Task\TaskExtension;
use Maestro\Extension\Workspace\WorkspaceExtension;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\MapResolver\Resolver;

class FileExtension implements Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        $container->register(PurgeDirectoryHandler::class, function (Container $container) {
            return new PurgeDirectoryHandler($container->getParameter(
                WorkspaceExtension::PARAM_WORKSPACE_PATH
            ));
        }, [
            TaskExtension::TAG_TASK_HANDLER => [
                'alias' => 'purge',
                'taskClass' => PurgeDirectoryTask::class,
            ]
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema)
    {
    }
}
