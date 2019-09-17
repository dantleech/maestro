<?php

namespace Maestro\Extension\Runner;

use Maestro\Extension\Runner\Command\Behavior\GraphBehavior;
use Maestro\Extension\Runner\Command\RunCommand;
use Maestro\Extension\Runner\Task\InitHandler;
use Maestro\Extension\Runner\Task\InitTask;
use Maestro\Extension\Runner\Extension\TaskHandlerDefinitionMap;
use Maestro\Extension\Runner\Loader\GraphConstructor;
use Maestro\Extension\Runner\Loader\ManifestLoader;
use Maestro\Extension\Runner\Loader\Processor\PrototypeExpandingProcessor;
use Maestro\Extension\Runner\Loader\Processor\TaskAliasExpandingProcessor;
use Maestro\Extension\Runner\Report\RunReport;
use Maestro\Library\GraphTask\GraphTaskScheduler;
use Maestro\Library\Task\Queue;
use Maestro\Library\Task\Worker;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\Console\ConsoleExtension;
use Phpactor\MapResolver\Resolver;

class RunnerExtension implements Extension
{
    const PARAM_WORKING_DIRECTORY = 'runner.working-directory';
    const PARAM_PURGE = 'runner.purge';
    const SERVICE_TASK_DEFINITIONS = 'runner.alias_to_class_map';
    const TAG_TASK_HANDLER = 'runner.tag.task_handler';

    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        $this->registerConsole($container);
        $this->registerLoader($container);
        $this->registerTask($container);
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
    }

    private function registerConsole(ContainerBuilder $container)
    {
        $container->register(RunCommand::class, function (Container $container) {
            return new RunCommand(
                $container->get(GraphBehavior::class),
                $container->get(RunReport::class)
            );
        }, [ ConsoleExtension::TAG_COMMAND => ['name' => 'run']]);
        
        $container->register(GraphBehavior::class, function (Container $container) {
            return new GraphBehavior(
                $container->get(ManifestLoader::class),
                $container->get(GraphConstructor::class),
                $container->get(GraphTaskScheduler::class),
                $container->get(Worker::class)
            );
        });

        $container->register(RunReport::class, function (Container $container) {
            return new RunReport();
        });
    }

    private function registerLoader(ContainerBuilder $container)
    {
        $container->register(ManifestLoader::class, function (Container $container) {
            return new ManifestLoader(
                $container->getParameter(self::PARAM_WORKING_DIRECTORY),
                [
                    new PrototypeExpandingProcessor(),
                    new TaskAliasExpandingProcessor(
                        $container->get(TaskHandlerDefinitionMap::class),
                    )
                ]
            );
        });

        $container->register(GraphConstructor::class, function (Container $container) {
            return new GraphConstructor($container->getParameter(self::PARAM_PURGE));
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
            self::TAG_TASK_HANDLER => [
                'taskClass' => InitTask::class,
                'alias' => 'init',
            ]
        ]);
    }
}
