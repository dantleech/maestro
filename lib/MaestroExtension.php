<?php

namespace Maestro;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\Console\ConsoleExtension;
use Maestro\Console\Command\ExecuteCommand;
use Maestro\Model\Maestro;
use Maestro\Console\Tty\SymfonyTtyManager;
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
use Maestro\Model\Tty\TtyManager\NullTtyManager;
use Maestro\Console\Progress\ProgressRegistry;
use Maestro\Console\Progress\SilentProgress;
use Maestro\Console\Progress\SimpleProgress;
use Maestro\Model\Job\QueueMonitor;

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
    const SERVICE_APPLICATOR = 'maestro.application.applicator';
    const SERVICE_COMMAND_RUNNER = 'maestro.application.command_runner';
    const SERVICE_CONSOLE_PROGRESS_REGISTRY = 'maestro.console.progress_registry';
    const TAG_PROGRESS = 'progress';
    const SERVICE_QUEUE_MONITOR = 'maestro.queue_monitor';
    const PARAM_QUEUE_CONCURRENCY = 'concurrency';

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
            self::PARAM_QUEUE_CONCURRENCY => 10,
        ]);
        $schema->setTypes([
            self::PARAM_PACKAGES => 'array'
        ]);
        $schema->setTypes([
            'concurrency' => 'integer',
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
        $container->register(self::SERVICE_COMMAND_RUNNER, function (Container $container) {
            return new CommandRunner(
                $container->get(self::SERVICE_PACKAGE_DEFINITIONS),
                $container->get(self::SERVICE_QUEUE_MANAGER),
                $container->get(self::SERVICE_WORKSPACE)
            );
        });

        $container->register(self::SERVICE_APPLICATOR, function (Container $container) {
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
                $container->get(self::SERVICE_COMMAND_RUNNER),
                $container->get(self::SERVICE_CONSOLE_QUEUE_REPORT)
            );
        }, [ ConsoleExtension::TAG_COMMAND => ['name'=> 'execute']]);

        $container->register('maestro.console.command.apply', function (Container $container) {
            return new ApplyCommand(
                $container->get(self::SERVICE_APPLICATOR),
                $container->get(self::SERVICE_CONSOLE_QUEUE_REPORT),
                $container->get(self::SERVICE_CONSOLE_PROGRESS_REGISTRY)
            );
        }, [ ConsoleExtension::TAG_COMMAND => ['name'=> 'apply']]);

        $container->register(self::SERVICE_CONSOLE_MANAGER, function (Container $container) {
            if ($container->get(ConsoleExtension::SERVICE_OUTPUT)->isVerbose()) {
                return new SymfonyTtyManager($container->get(ConsoleExtension::SERVICE_OUTPUT));
            }

            return new NullTtyManager();
        });

        $container->register(self::SERVICE_CONSOLE_QUEUE_REPORT, function (Container $container) {
            return new TableQueueReport($container->get(ConsoleExtension::SERVICE_OUTPUT));
        });

        $container->register(self::SERVICE_CONSOLE_PROGRESS_REGISTRY, function (Container $container) {
            $progressMap = [];
            foreach ($container->getServiceIdsForTag(self::TAG_PROGRESS) as $serviceId => $attrs) {
                if (!isset($attrs['name'])) {
                    throw new RuntimeException(sprintf(
                        'Progres service "%s" must be registered with a "name" attribute',
                        $serviceId
                    ));
                }
                $progressMap[$attrs['name']] = $container->get($serviceId);
            }

            return new ProgressRegistry($progressMap);
        });

        $container->register('maestro.console.progress.silent', function (Container $container) {
            return new SilentProgress();
        }, [ self::TAG_PROGRESS => [ 'name' => 'silent' ]]);

        $container->register('maestro.console.progress.graph', function (Container $container) {
            return new SimpleProgress(
                $container->get(self::SERVICE_QUEUE_MONITOR)
            );
        }, [ self::TAG_PROGRESS => [ 'name' => 'graph' ]]);
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
                $container->get(self::SERVICE_JOB_DISPATCHER),
                $container->get(self::SERVICE_QUEUE_MONITOR),
                $container->getParameter(self::PARAM_QUEUE_CONCURRENCY)
            );
        });

        $container->register(self::SERVICE_QUEUE_MONITOR, function (Container $container) {
            return new QueueMonitor();
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
