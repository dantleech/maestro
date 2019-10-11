<?php

namespace Maestro\Extension\Publisher;

use Maestro\Extension\Publisher\Task\PublishHandler;
use Maestro\Extension\Publisher\Task\PublishTask;
use Maestro\Extension\Runner\RunnerExtension;
use Maestro\Extension\Task\TaskExtension;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\MapResolver\Resolver;

class PublisherExtension implements Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        $container->register(PublishHandler::class, function (Container $container) {
            return new PublishHandler(
                $container->getParameter(RunnerExtension::PARAM_WORKING_DIRECTORY)
            );
        }, [ TaskExtension::TAG_TASK_HANDLER => [
            'alias' => 'publish',
            'taskClass' => PublishTask::class,
        ]]);
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema)
    {
    }
}
