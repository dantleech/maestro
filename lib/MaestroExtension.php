<?php

namespace Maestro;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\Console\ConsoleExtension;
use Maestro\Console\RunCommand;
use Maestro\Model\Unit\Invoker;
use Maestro\Model\Unit\Registry\LazyCallbackRegistry;
use Phpactor\MapResolver\Resolver;
use Maestro\Model\Unit\Config\Resolver as ConfigResolver;

use RuntimeException;

class MaestroExtension implements Extension
{
    const TAG_UNIT = 'unit';
    const SERVICE_INVOKER = 'maestro.unit.invoker';


    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        $this->loadConsole($container);
        $this->loadUnit($container);
    }

    private function loadConsole(ContainerBuilder $container)
    {
        $container->register('maestro.console.command.run', function (Container $container) {
            return new RunCommand(
                $container->get(self::SERVICE_INVOKER)
            );
        }, [ ConsoleExtension::TAG_COMMAND => ['name'=> 'run']]);
    }

    private function loadUnit(ContainerBuilder $container)
    {
        $container->register(self::SERVICE_INVOKER, function (Container $container) {
            return new Invoker(
                $container->get('maestro.unit.registry'),
                $container->get('maestro.unit.resolver')
            );
        });

        $container->register('maestro.unit.registry', function (Container $container) {
            $map = [];

            foreach ($container->getServiceIdsForTag(self::TAG_UNIT) as $serviceId => $attrs) {
                if (!isset($attrs['name'])) {
                    throw new RuntimeException(sprintf(
                        'Unit container service "%s" must define a "name" attribute',
                        $serviceId
                    ));
                }

                $map[$attrs['name']] = function () use ($container, $serviceId) {
                    return $container->get($serviceId);
                };
            }

            return new LazyCallbackRegistry($map);
        });

        $container->register('maestro.unit.resolver', function (Container $container) {
            return new ConfigResolver();
        });
    }
}
