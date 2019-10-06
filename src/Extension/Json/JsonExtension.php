<?php

namespace Maestro\Extension\Json;

use Maestro\Extension\Json\Task\JsonFileHandler;
use Maestro\Extension\Json\Task\JsonFileTask;
use Maestro\Extension\Task\TaskExtension;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\MapResolver\Resolver;

class JsonExtension implements Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        $container->register(JsonFileHandler::class, function (Container $container) {
            return new JsonFileHandler();
        }, [
            TaskExtension::TAG_TASK_HANDLER => [
                'taskClass' => JsonFileTask::class,
                'alias' => 'json_file',
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
