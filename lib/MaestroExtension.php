<?php

namespace Maestro;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\Console\ConsoleExtension;
use Maestro\Console\Command\Exec;
use Maestro\Model\Unit\Invoker;
use Maestro\Model\Maestro;
use Maestro\Console\SymfonyConsoleManager;
use Maestro\Model\Unit\Registry\LazyCallbackRegistry;
use Phpactor\MapResolver\Resolver;
use Maestro\Model\Unit\Config\RealResolver;
use Maestro\Model\Unit\Config\ParameterReplacementResolver;

class MaestroExtension implements Extension
{
    const TAG_UNIT = 'unit';
    const SERVICE_INVOKER = 'maestro.unit.invoker';
    const SERVICE_CONSOLE_MANAGER = 'maestro.console.manager';


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

        $container->register('maestro', function (Container $container) {
            return new Maestro(
                $container->get(self::SERVICE_INVOKER),
                $container->get(self::SERVICE_CONSOLE_MANAGER)
            );
        });
    }

    private function loadConsole(ContainerBuilder $container)
    {
        $container->register('maestro.console.command.run', function (Container $container) {
            return new Exec(
                $container->get('maestro')
            );
        }, [ ConsoleExtension::TAG_COMMAND => ['name'=> 'run']]);

        $container->register(self::SERVICE_CONSOLE_MANAGER, function (Container $container) {
            return new SymfonyConsoleManager($container->get(ConsoleExtension::SERVICE_OUTPUT));
        });
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
            return new ParameterReplacementResolver(new RealResolver());
        });
    }
}
