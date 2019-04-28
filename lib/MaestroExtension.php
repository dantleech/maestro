<?php

namespace Maestro;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\Console\ConsoleExtension;
use Maestro\Console\Command\ExecuteCommand;
use Maestro\Model\Maestro;
use Maestro\Adapter\Symfony\SymfonyConsoleManager;
use Phpactor\MapResolver\Resolver;
use Maestro\Service\CommandRunner;
use Maestro\Model\Job\QueueDispatcher\RealQueueDispatcher;
use Maestro\Model\Package\PackageDefinitions;
use Maestro\Model\Job\Dispatcher\LazyDispatcher;
use Maestro\Adapter\Amp\Job\ProcessHandler;
use RuntimeException;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use XdgBaseDir\Xdg;
use Maestro\Model\Package\Workspace;
use Maestro\Adapter\Amp\Job\InitializePackageHandler;
use Maestro\Console\Command\ApplyCommand;
use Maestro\Service\Applicator;
use Maestro\Adapter\Twig\Job\ApplyTemplateHandler;
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
    const PARAM_TEMPLATE_PATHS = 'template_paths';

    const TAG_JOB_HANDLER = 'maestro.job_handler';

    const SERVICE_TWIG = 'maestro.twig';
    const SERVICE_CONSOLE_QUEUE_REPORT = 'maestro.console.queue_report';
    const SERVICE_APPLY_TEMPLATE_HANDLER = 'maestro.adapter.twig.handler.apply_template';
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
            self::PARAM_TEMPLATE_PATHS => [
                getcwd()
            ]
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

        $this->loadTwig($container);
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
                $container->get(self::SERVICE_WORKSPACE)
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
        $container->register(self::SERVICE_QUEUE_MANAGER, function (Container $container) {
            $queueModifiers = [];
            return new RealQueueDispatcher(
                $container->get(self::SERVICE_JOB_DISPATCHER)
            );
        });
        $container->register(self::SERVICE_JOB_DISPATCHER, function (Container $container) {
            $handlers = [];
            foreach ($container->getServiceIdsForTag(self::TAG_JOB_HANDLER) as $serviceId => $attrs) {
                if (!isset($attrs['id'])) {
                    throw new RuntimeException(sprintf(
                        'Service "%s" must have an ID parameter (which should probably it\'s FQN or otherwise
                        what was specified in it\'s related job).',
                        $serviceId
                    ));
                }
                $handlers[$attrs['id']] = function () use ($container, $serviceId) {
                    return $container->get($serviceId);
                };
            }
            return new LazyDispatcher($handlers);
        });

        $container->register('maestro.adapter.amp.handler.process', function (Container $container) {
            return new ProcessHandler(
                $container->get(self::SERVICE_CONSOLE_MANAGER)
            );
        }, [ self::TAG_JOB_HANDLER => [ 'id' => ProcessHandler::class ]]);

        $container->register('maestro.adapter.amp.handler.initialize_package', function (Container $container) {
            return new InitializePackageHandler(
                $container->get(self::SERVICE_WORKSPACE)
            );
        }, [ self::TAG_JOB_HANDLER => [ 'id' => InitializePackageHandler::class ]]);

        $container->register(self::SERVICE_APPLY_TEMPLATE_HANDLER, function (Container $container) {
            return new ApplyTemplateHandler(
                $container->get(self::SERVICE_CONSOLE_MANAGER),
                $container->get(self::SERVICE_WORKSPACE),
                $container->get(self::SERVICE_TWIG),
                $container->getParameter(self::PARAM_PARAMETERS)
            );
        }, [ self::TAG_JOB_HANDLER => [ 'id' => ApplyTemplateHandler::class ]]);
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

    private function loadTwig(ContainerBuilder $container)
    {
        $container->register(self::SERVICE_TWIG, function (Container $container) {
            return new Environment(
                new FilesystemLoader($container->getParameter(self::PARAM_TEMPLATE_PATHS)),
                [
                    'strict_variables' => true,
                    'auto_reload' => false,
                    'cache' => false,
                ]
            );
        });
    }
}
