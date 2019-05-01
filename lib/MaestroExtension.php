<?php

namespace Maestro;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\Console\ConsoleExtension;
use Maestro\Console\Command\ExecuteCommand;
use Maestro\Model\Maestro;
use Maestro\Console\SymfonyConsoleManager;
use Phpactor\MapResolver\Resolver;
use Maestro\Service\CommandRunner;
use Maestro\Model\Job\QueueDispatcher\RealQueueDispatcher;
use Maestro\Model\Job\Dispatcher\LazyDispatcher;
use RuntimeException;
use XdgBaseDir\Xdg;
use Maestro\Model\Package\Workspace;
use Maestro\Console\Command\ApplyCommand;
use Maestro\Service\Applicator;
use Maestro\Model\Package\PackageDefinitionsLoader;
use Maestro\Console\Report\TableQueueReport;

class MaestroExtension implements Extension
{
    const TAG_UNIT = 'unit';

    const SERVICE_INVOKER = 'maestro.unit.invoker';
    const SERVICE_CONSOLE_MANAGER = 'maestro.console.manager';
    const SERVICE_QUEUE_MANAGER = 'maestro.model.queue.manager';
    const SERVICE_PACKAGE_DEFINITIONS = 'maestro.model.package.definitions';
    const SERVICE_JOB_DISPATCHER = 'maestro.job.dispatcher';
    const SERVICE_WORKSPACE = 'maestro.package.workspace';

    const PARAM_WORKSPACE_PATH = 'workspace_path';
    const PARAM_PACKAGES = 'packages';
    const PARAM_PARAMETERS = 'parameters';
    const TAG_JOB_HANDLER = 'maestro.job_handler';

    const SERVICE_CONSOLE_QUEUE_REPORT = 'maestro.console.queue_report';
    const PARAM_PROTOTYPES = 'prototypes';

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema)
    {
        $xdg = new Xdg();
        $schema->setDefaults([
            self::PARAM_PACKAGES => [],
            self::PARAM_WORKSPACE_PATH => $xdg->getHomeDataDir() . '/maestro',
            self::PARAM_PARAMETERS => [],
            self::PARAM_PROTOTYPES => [],
        ]);
        $schema->setTypes([
            self::PARAM_PACKAGES => 'array'
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        $this->loadApplication($container);
        $this->loadPackage($container);
        $this->loadConsole($container);
        $this->loadJob($container);
    }

    private function loadApplication(ContainerBuilder $container)
    {
        $container->register('maestro.application.command_runner', function (Container $container) {
            return new CommandRunner(
                $container->get(self::SERVICE_PACKAGE_DEFINITIONS),
                $container->get(self::SERVICE_QUEUE_MANAGER),
                $container->get(self::SERVICE_WORKSPACE)
            );
        });

        $container->register('maestro.application.applicator', function (Container $container) {
            return new Applicator(
                $container->get(self::SERVICE_PACKAGE_DEFINITIONS),
                $container->get(self::SERVICE_QUEUE_MANAGER),
                $container->get(self::SERVICE_WORKSPACE),
                $container->get('maestro.job.class_map')
            );
        });
    }

    private function loadConsole(ContainerBuilder $container)
    {
        $container->register('maestro.console.command.execute', function (Container $container) {
            return new ExecuteCommand(
                $container->get('maestro.application.command_runner'),
                $container->get(self::SERVICE_CONSOLE_QUEUE_REPORT)
            );
        }, [ ConsoleExtension::TAG_COMMAND => ['name'=> 'execute']]);

        $container->register('maestro.console.command.apply', function (Container $container) {
            return new ApplyCommand(
                $container->get('maestro.application.applicator'),
                $container->get(self::SERVICE_CONSOLE_QUEUE_REPORT)
            );
        }, [ ConsoleExtension::TAG_COMMAND => ['name'=> 'apply']]);

        $container->register(self::SERVICE_CONSOLE_MANAGER, function (Container $container) {
            return new SymfonyConsoleManager($container->get(ConsoleExtension::SERVICE_OUTPUT));
        });

        $container->register(self::SERVICE_CONSOLE_QUEUE_REPORT, function (Container $container) {
            return new TableQueueReport();
        });
    }

    private function loadJob(ContainerBuilder $container)
    {
        $container->register('maestro.job.class_map', function (Container $container) {
            $map = [];
            foreach ($container->getServiceIdsForTag(self::TAG_JOB_HANDLER) as $serviceId => $attrs) {
                if (!isset($attrs['job'])) {
                    throw new RuntimeException(sprintf(
                        'Job handler service "%s" must specify a "job" '.
                        'attribute during registation with the FQN of the job it '.
                        'handles',
                        $serviceId
                    ));
                }

                if (!isset($attrs['type'])) {
                    continue;
                }

                $map[$attrs['type']] = $attrs['job'];
            }

            return $map;
        });
        $container->register(self::SERVICE_QUEUE_MANAGER, function (Container $container) {
            $queueModifiers = [];
            return new RealQueueDispatcher(
                $container->get(self::SERVICE_JOB_DISPATCHER)
            );
        });
        $container->register(self::SERVICE_JOB_DISPATCHER, function (Container $container) {
            $handlers = [];
            foreach ($container->getServiceIdsForTag(self::TAG_JOB_HANDLER) as $serviceId => $attrs) {
                if (!isset($attrs['job'])) {
                    throw new RuntimeException(sprintf(
                        'Job handler service "%s" must specify a "job" '.
                        'attribute during registation with the FQN of the job it '.
                        'handles',
                        $serviceId
                    ));
                }

                $handlers[$attrs['job']] = function () use ($container, $serviceId) {
                    return $container->get($serviceId);
                };
            }
            return new LazyDispatcher($handlers);
        });
    }

    private function loadPackage(ContainerBuilder $container)
    {
        $container->register(self::SERVICE_PACKAGE_DEFINITIONS, function (Container $container) {
            return (new PackageDefinitionsLoader())->load(
                $container->getParameter(self::PARAM_PACKAGES),
                $container->getParameter(self::PARAM_PROTOTYPES)
            );
        });
        $container->register(self::SERVICE_WORKSPACE, function (Container $container) {
            return Workspace::create($container->getParameter(self::PARAM_WORKSPACE_PATH));
        });
    }
}
