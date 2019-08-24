<?php

namespace Maestro\Extension\Version;

use Maestro\Extension\Maestro\Command\Behavior\GraphBehavior;
use Maestro\Extension\Version\Console\VersionReport;
use Maestro\Extension\Version\Command\VersionReportCommand;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\Console\ConsoleExtension;
use Phpactor\MapResolver\Resolver;

class VersionExtension implements Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        $container->register(VersionReportCommand::class, function (Container $container) {
            return new VersionReportCommand(
                $container->get(GraphBehavior::class),
                $container->get(VersionReport::class)
            );
        }, [ ConsoleExtension::TAG_COMMAND => [
            'name' => 'version:report',
        ]]);
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema)
    {
    }
}
