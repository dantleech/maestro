<?php

namespace Maestro\Extension\CoreExtension;

use Maestro\Extension\CoreExtension\Unit\Root;
use Maestro\MaestroExtension;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\MapResolver\Resolver;

class CoreExtension implements Extension
{
    public function load(ContainerBuilder $container)
    {
        $container->register('maestro_core.unit.root', function (Container $container) {
            return new Root($container->get(MaestroExtension::SERVICE_INVOKER));
        }, [ MaestroExtension::TAG_UNIT => ['name' => 'root']]);
    }

    public function configure(Resolver $schema)
    {
    }
}
