<?php

namespace Maestro;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\Console\ConsoleExtension;
use Maestro\Console\RunCommand;
use Maestro\Model\Unit\UnitRegistry\LazyUnitRegistry;
use Maestro\Model\Maestro;
use Maestro\Model\Unit\UnitExecutor;
use Maestro\Model\Unit\UnitParameterResolver;
use Maestro\Model\ParameterResolverFactory;
use Phpactor\MapResolver\Resolver;
use RuntimeException;

class MaestroExtension implements Extension
{
    const SERVICE_MAESTRO = 'maestro.maestro';
    const TAG_UNIT = 'maestro.unit';

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
        $this->loadUtils($container);
        $this->loadUnitInfrastructure($container);
    }

    private function loadConsole(ContainerBuilder $container)
    {
        $container->register('maestro.console.command.run', function (Container $container) {
            return new RunCommand(
                $container->get(self::SERVICE_MAESTRO)
            );
        }, [ ConsoleExtension::TAG_COMMAND => ['name'=> 'run']]);
    }

    private function loadUnitInfrastructure(ContainerBuilder $container)
    {
        $container->register(self::SERVICE_MAESTRO, function (Container $container) {
            return new Maestro(
                $container->get('maestro.model.unit_loader')
            );
        });

        $container->register('maestro.model.unit.parameter_resolver', function (Container $container) {
            return new UnitParameterResolver(
                $container->get('maestro.parameter_resolver_factory')
            );
        });
        
        $container->register('maestro.model.unit_loader', function (Container $container) {
            return new UnitExecutor(
                $container->get('maestro.model.unit.parameter_resolver'),
                $container->get('maestro.model.unit_registry')
            );
        });
        
        $container->register('maestro.model.unit_registry', function (Container $container) {
            $map = [];
            foreach ($container->getServiceIdsForTag(self::TAG_UNIT) as $serviceId => $attrs) {
                if (!isset($attrs['name'])) {
                    throw new RuntimeException(sprintf(
                        'Unit service "%s" has no "name" tag, each unit service must define a `name` attribute',
                        $serviceId
                    ));
                }
                $map[$attrs['name']] = $serviceId;
            }
        
            return new LazyUnitRegistry($map, function (string $serviceId) use ($container) {
                return $container->get($serviceId);
            });
        });
    }

    private function loadUtils(ContainerBuilder $container)
    {
        $container->register('maestro.parameter_resolver_factory', function () {
            return new ParameterResolverFactory();
        });
    }
}
