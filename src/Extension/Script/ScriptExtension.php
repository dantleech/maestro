<?php

namespace Maestro\Extension\Script;

use Maestro\Extension\Script\Model\ScriptRunner;
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
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema)
    {
    }
}
