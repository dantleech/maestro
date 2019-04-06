<?php

namespace Phpactor\Extension\Maestro;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\Console\ConsoleExtension;
use Phpactor\Extension\Maestro\Console\Logger\ConsoleLogger;
use Phpactor\Extension\Maestro\Console\RunCommand;
use Phpactor\Extension\Maestro\Job\Process\ProcessHandler;
use Phpactor\Extension\Maestro\Model\Console\ConsolePool;
use Phpactor\Extension\Maestro\Model\Job\JobHandlerRegistry;
use Phpactor\Extension\Maestro\Model\Queue\QueueDispatcher;
use Phpactor\Extension\Maestro\Model\Queue\QueueRegistry;
use Phpactor\Extension\Maestro\Model\Unit\UnitRegistry\LazyUnitRegistry;
use Phpactor\Extension\Maestro\Model\Logger;
use Phpactor\Extension\Maestro\Model\Maestro;
use Phpactor\Extension\Maestro\Model\StateMachine\Machine\LoggingStateMachine;
use Phpactor\Extension\Maestro\Model\StateMachine\Machine\RealStateMachine;
use Phpactor\Extension\Maestro\Model\StateMachine\State\LoggingState;
use Phpactor\Extension\Maestro\Model\Unit\UnitExecutor;
use Phpactor\Extension\Maestro\Model\Unit\UnitRegistry\EagerUnitRegistry;
use Phpactor\Extension\Maestro\Module\System\ConfigLoaded;
use Phpactor\Extension\Maestro\Module\System\Initialized;
use Phpactor\Extension\Maestro\Unit\CommandUnit;
use Phpactor\Extension\Maestro\Unit\CopyUnit;
use Phpactor\Extension\Maestro\Unit\PackageWorkspaceUnit;
use Phpactor\Extension\Maestro\Unit\SequenceUnit;
use Phpactor\MapResolver\Resolver;
use RuntimeException;
use Webmozart\PathUtil\Path;
use XdgBaseDir\Xdg;

class MaestroExtension implements Extension
{
    const SERVICE_MAESTRO = 'maestro.maestro';
    const TAG_STATE = 'maestro.state';
    const SERVICE_CONSOLE_LOGGER = 'maestro.console.logger';
    const WORKSPACE_PATH = 'workspace_path';
    const TAG_UNIT = 'maestro.unit';
    const TAG_JOB_HANDLER = 'maestro.job_handler';

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema)
    {
        $xdg = new Xdg();
        $workspace = Path::join($xdg->getHomeDataDir(), 'maestro-php');

        $schema->setDefaults([
            self::WORKSPACE_PATH => $workspace
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        $this->loadConsole($container);
        $this->loadUnitInfrastructure($container);
        $this->loadUnits($container);
        $this->loadJobInfrastructure($container);
        $this->loadJobHandlers($container);
    }

    private function loadConsole(ContainerBuilder $container)
    {
        $container->register('maestro.console.command.run', function (Container $container) {
            return new RunCommand(
                $container->get(self::SERVICE_MAESTRO),
                $container->get('maestro.model.console_pool')
            );
        }, [ ConsoleExtension::TAG_COMMAND => ['name'=> 'run']]);

        $container->register('maestro.model.console_pool', function (Container $container) {
            return new ConsolePool();
        });
    }

    private function loadUnitInfrastructure(ContainerBuilder $container)
    {
        $container->register(self::SERVICE_MAESTRO, function (Container $container) {
            return new Maestro(
                $container->get('maestro.model.unit_loader'),
                $container->get('maestro.model.queue_registry'),
                $container->get('maestro.model.queue_dispatcher')
            );
        });
        
        $container->register('maestro.model.unit_loader', function (Container $container) {
            return new UnitExecutor($container->get('maestro.model.unit_registry'));
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

    private function loadUnits(ContainerBuilder $container)
    {
        $container->register('maestro.unit.package_workspace', function (Container $container) {
            return new PackageWorkspaceUnit(
                $container->get('maestro.model.unit_loader'),
                $container->get('maestro.model.queue_registry'),
                $container->get('maestro.model.console_pool'),
                $container->getParameter(self::WORKSPACE_PATH)
            );
        }, [ self::TAG_UNIT => [ 'name' => 'package_workspace' ]]);

        $container->register('maestro.unit.command', function (Container $container) {
            return new CommandUnit(
                $container->get('maestro.model.queue_registry')
            );
        }, [ self::TAG_UNIT => [ 'name' => 'command' ]]);

        $container->register('maestro.unit.sequence', function (Container $container) {
            return new SequenceUnit($container->get('maestro.model.unit_loader'));
        }, [ self::TAG_UNIT => [ 'name' => 'sequence' ]]);
    }

    private function loadJobInfrastructure(ContainerBuilder $container)
    {
        $container->register('maestro.model.queue_registry', function (Container $contianer) {
            return new QueueRegistry();
        });

        $container->register('maestro.model.queue_dispatcher', function (Container $container) {
            return new QueueDispatcher($container->get('maestro.model.job_handler_registry'));
        });

        $container->register('maestro.model.job_handler_registry', function (Container $container) {
            $handlers = [];
            foreach ($container->getServiceIdsForTag(self::TAG_JOB_HANDLER) as $serviceId => $attrs) {
                $handler = $container->get($serviceId);
                $handlers[get_class($handler)] = $handler;
            }
            return new JobHandlerRegistry($handlers);
        });
    }

    private function loadJobHandlers(ContainerBuilder $container)
    {
        $container->register('maestro.job.process.handler', function (Container $container) {
            return new ProcessHandler($container->get('maestro.model.console_pool'));
        }, [ self::TAG_JOB_HANDLER => []]);
    }
}
