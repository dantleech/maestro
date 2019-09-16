<?php

namespace Maestro\Extension\Script;

use Maestro\Extension\Runner\RunnerExtension;
use Maestro\Extension\Script\Model\ScriptRunner;
use Maestro\Extension\Script\Task\ScriptHandler;
use Maestro\Extension\Script\Task\ScriptTask;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\MapResolver\Resolver;

class ScriptExtension implements Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        $container->register(ScriptRunner::class, function (Container $container) {
            return new ScriptRunner($container->get(LoggingExtension::SERVICE_LOGGER));
        });

        $container->register(ScriptHandler::class, function (Container $container) {
            return new ScriptHandler($container->get(ScriptRunner::class));
        }, [
            RunnerExtension::TAG_TASK_HANDLER => [
                'taskClass' => ScriptTask::class,
                'alias' => 'script',
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
