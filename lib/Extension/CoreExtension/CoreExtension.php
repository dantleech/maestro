<?php

namespace Maestro\Extension\CoreExtension;

use Maestro\Extension\CoreExtension\Unit\RootUnit;
use Maestro\MaestroExtension;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\MapResolver\Resolver;

class CoreExtension implements Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        $container->register('maestro.core.unit.root', function (Container $container) {
            return new RootUnit($container->get(MaestroExtension::SERVICE_UNIT_EXECUTOR));
        }, [ MaestroExtension::TAG_UNIT => [ 'name' => 'root' ]]);
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema)
    {
    }
}
