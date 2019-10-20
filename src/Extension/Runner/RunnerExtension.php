<?php

namespace Maestro\Extension\Runner;

use Maestro\Extension\Runner\Model\Loader\ManifestNode;
use Maestro\Extension\Runner\Model\Loader\PathExpander;
use Maestro\Library\Report\ReportRegistry;
use Maestro\Extension\Report\ReportExtension;
use Maestro\Extension\Runner\Command\Behavior\GraphBehavior;
use Maestro\Extension\Runner\Command\RunCommand;
use Maestro\Extension\Runner\Command\TaskCommand;
use Maestro\Extension\Runner\Console\MethodToInputDefinitionConverter;
use Maestro\Extension\Runner\Model\TagParser;
use Maestro\Extension\Runner\Logger\MaestroColoredLineFormatter;
use Maestro\Extension\Runner\Task\InitHandler;
use Maestro\Extension\Runner\Task\InitTask;
use Maestro\Extension\Task\Extension\TaskHandlerDefinitionMap;
use Maestro\Extension\Runner\Model\Loader\GraphConstructor;
use Maestro\Extension\Runner\Model\Loader\ManifestLoader;
use Maestro\Extension\Runner\Model\Loader\Processor\PrototypeExpandingProcessor;
use Maestro\Extension\Runner\Model\Loader\Processor\TaskAliasExpandingProcessor;
use Maestro\Extension\Runner\Report\RunReport;
use Maestro\Extension\Runner\Task\PackageHandler;
use Maestro\Extension\Runner\Task\PackageTask;
use Maestro\Extension\Task\TaskExtension;
use Maestro\Library\Graph\GraphTaskScheduler;
use Maestro\Library\Task\Queue;
use Maestro\Library\Task\Worker;
use Maestro\Library\Workspace\WorkspaceManager;
use Monolog\Formatter\JsonFormatter;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\Console\ConsoleExtension;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\MapResolver\Resolver;

class RunnerExtension implements Extension
{
    const PARAM_WORKING_DIRECTORY = 'runner.workingDirectory';
    const PARAM_MANIFEST_PATH = 'runner.manifestPath';
    const PARAM_PURGE = 'runner.purge';

    const SERVICE_TASK_DEFINITIONS = 'runner.aliasToClassMap';

    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        $this->registerConsole($container);
        $this->registerLoader($container);
        $this->registerTask($container);
        $this->registerLogging($container);
        $this->registerReport($container);
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema)
    {
        $schema->setDefaults([
            self::PARAM_WORKING_DIRECTORY => getcwd(),
            self::PARAM_PURGE => false
        ]);
        $schema->setRequired([
            self::PARAM_MANIFEST_PATH
        ]);
    }

    private function registerConsole(ContainerBuilder $container)
    {
        $container->register(RunCommand::class, function (Container $container) {
            return new RunCommand(
                $container->get(GraphBehavior::class)
            );
        }, [ ConsoleExtension::TAG_COMMAND => ['name' => 'run']]);

        $container->register(TaskCommand::class, function (Container $container) {
            return new TaskCommand(
                $container->get(GraphBehavior::class),
                $container->get(TaskHandlerDefinitionMap::class),
                new MethodToInputDefinitionConverter(),
            );
        }, [ ConsoleExtension::TAG_COMMAND => ['name' => 'task']]);
        
        $container->register(GraphBehavior::class, function (Container $container) {
            return new GraphBehavior(
                $container->get(GraphConstructor::class),
                $container->get(GraphTaskScheduler::class),
                $container->get(Worker::class),
                $container->get(LoggingExtension::SERVICE_LOGGER),
                $container->get(Queue::class),
                new TagParser(),
                $container->get(ReportRegistry::class)
            );
        });
    }

    private function registerLoader(ContainerBuilder $container)
    {
        $container->register(ManifestNode::class, function (Container $container) {
            return $this->createManifestLoader($container)->load(
                $container->getParameter(self::PARAM_MANIFEST_PATH)
            );
        });

        $container->register(GraphConstructor::class, function (Container $container) {
            return new GraphConstructor(
                new PathExpander(),
                $container->get(ManifestNode::class)
            );
        });
    }

    private function registerTask(ContainerBuilder $container)
    {
        $container->register(GraphTaskScheduler::class, function (Container $container) {
            return new GraphTaskScheduler($container->get(Queue::class));
        });

        $container->register(InitHandler::class, function (Container $container) {
            return new InitHandler();
        }, [
            TaskExtension::TAG_TASK_HANDLER => [
                'taskClass' => InitTask::class,
                'alias' => 'init',
            ]
        ]);

        $container->register(PackageHandler::class, function (Container $container) {
            return new PackageHandler(
                $container->get(WorkspaceManager::class),
                $container->getParameter(self::PARAM_PURGE)
            );
        }, [
            TaskExtension::TAG_TASK_HANDLER => [
                'taskClass' => PackageTask::class,
                'alias' => 'package',
            ]
        ]);
    }

    private function registerLogging(ContainerBuilder $container): void
    {
        $container->register(JsonFormatter::class, function (Container $container) {
            return new JsonFormatter();
        }, [ LoggingExtension::TAG_FORMATTER => ['alias' => 'json']]);

        $container->register(MaestroColoredLineFormatter::class, function (Container $container) {
            return new MaestroColoredLineFormatter(null, "[%elapsed%] %message% %context% %extra%\n", 'U.u');
        }, [ LoggingExtension::TAG_FORMATTER => ['alias' => 'console']]);
    }

    private function registerReport(ContainerBuilder $container)
    {
        $container->register(RunReport::class, function (Container $container) {
            return new RunReport(
                $container->get(ConsoleExtension::SERVICE_OUTPUT)
            );
        }, [
            ReportExtension::TAG_REPORT => [
                'name' => 'run'
            ]
        ]);
    }

    private function createManifestLoader(Container $container): ManifestLoader
    {
        return new ManifestLoader(
            $container->getParameter(self::PARAM_WORKING_DIRECTORY),
            [
                new PrototypeExpandingProcessor(),
                new TaskAliasExpandingProcessor(
                    $container->get(TaskHandlerDefinitionMap::class),
                )
            ]
        );
    }
}
