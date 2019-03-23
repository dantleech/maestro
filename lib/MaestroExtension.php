<?php

namespace Phpactor\Extension\Maestro;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\Console\ConsoleExtension;
use Phpactor\Extension\Maestro\Console\Logger\ConsoleLogger;
use Phpactor\Extension\Maestro\Console\RunCommand;
use Phpactor\Extension\Maestro\Model\Logger;
use Phpactor\Extension\Maestro\Model\Maestro;
use Phpactor\Extension\Maestro\Model\StateMachine\Machine\LoggingStateMachine;
use Phpactor\Extension\Maestro\Model\StateMachine\Machine\RealStateMachine;
use Phpactor\Extension\Maestro\Model\StateMachine\State\LoggingState;
use Phpactor\Extension\Maestro\Module\System\ConfigLoaded;
use Phpactor\Extension\Maestro\Module\System\Initialized;
use Phpactor\MapResolver\Resolver;

class MaestroExtension implements Extension
{
    const SERVICE_MAESTRO = 'maestro.maestro';
    const TAG_STATE = 'maestro.state';
    const SERVICE_CONSOLE_LOGGER = 'maestro.console.logger';

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
        $container->register('maestro.console.command.run', function (Container $container) {
            return new RunCommand($container->get(self::SERVICE_MAESTRO));
        }, [ ConsoleExtension::TAG_COMMAND => ['name'=> 'run']]);

        $container->register(self::SERVICE_MAESTRO, function (Container $container) {
            return new Maestro($container->get('maestro.model.state_machine'));
        });

        $container->register('maestro.model.state_machine', function (Container $container) {
            $states = [];

            foreach (array_keys($container->getServiceIdsForTag(self::TAG_STATE)) as $serviceId) {
                $states[] = new LoggingState($container->get($serviceId), $container->get(self::SERVICE_CONSOLE_LOGGER));
            }

            return new LoggingStateMachine(
                new RealStateMachine($states),
                $container->get(self::SERVICE_CONSOLE_LOGGER)
            );
        });

        $container->register(self::SERVICE_CONSOLE_LOGGER, function (Container $container) {
            return new ConsoleLogger($container->get(ConsoleExtension::SERVICE_OUTPUT));
        });

        $this->registerStates($container);
    }

    private function registerStates(ContainerBuilder $container): void
    {
        $container->register('maesto.state.system.initialized', function (Container $container) {
            return new Initialized();
        }, [ self::TAG_STATE => []]);
        
        $container->register('maesto.state.system.config_loaded', function (Container $container) {
            return new ConfigLoaded();
        }, [ self::TAG_STATE => []]);
    }
}
