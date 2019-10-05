<?php

namespace Maestro\Extension\Task;

use Maestro\Extension\Task\Command\DebugTaskCommand;
use Maestro\Extension\Task\Extension\TaskHandlerDefinition;
use Maestro\Extension\Task\Extension\TaskHandlerDefinitionMap;
use Maestro\Extension\Task\TaskRunner\TaskRunnerInjectingRunner;
use Maestro\Library\Instantiator\Instantiator;
use Maestro\Library\Task\Queue;
use Maestro\Library\Task\Queue\FifoQueue;
use Maestro\Library\Task\TaskHandlerRegistry;
use Maestro\Library\Task\TaskRunner;
use Maestro\Library\Task\TaskRunner\InvokingTaskRunner;
use Maestro\Extension\Task\TaskRunner\LoggingTaskRunner;
use Maestro\Library\Task\Task\NullHandler;
use Maestro\Library\Task\Task\NullTask;
use Maestro\Library\Task\Worker;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\Console\ConsoleExtension;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\MapResolver\Resolver;

class TaskExtension implements Extension
{
    const TAG_TASK_HANDLER = 'runner.tag.taskHandler';

    const PARAM_MILLISLEEP = 'task.milliSleep';
    const PARAM_CONCURRENCY = 'task.concurrency';


    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        $container->register(Worker::class, function (Container $container) {
            return new Worker(
                $container->get(TaskRunner::class),
                $container->get(Queue::class),
                $container->getParameter(self::PARAM_MILLISLEEP),
                $container->getParameter(self::PARAM_CONCURRENCY),
            );
        });

        $container->register(TaskRunner::class, function (Container $container) {
            return new TaskRunnerInjectingRunner(new LoggingTaskRunner(
                new InvokingTaskRunner($container->get(TaskHandlerRegistry::class)),
                $container->get(LoggingExtension::SERVICE_LOGGER)
            ));
        });

        $container->register(Queue::class, function (Container $container) {
            return new FifoQueue();
        });

        $container->register(TaskHandlerDefinitionMap::class, function (Container $container) {
            $definitions = [];

            foreach ($container->getServiceIdsForTag(self::TAG_TASK_HANDLER) as $serviceId => $attrs) {
                $attrs = array_merge([
                    'serviceId' => $serviceId
                ], $attrs);
                $definitions[] = Instantiator::instantiate(TaskHandlerDefinition::class, $attrs);
            }

            return new TaskHandlerDefinitionMap($definitions);
        });

        $container->register(TaskHandlerRegistry::class, function (Container $container) {
            $map = [];
            foreach ($container->get(TaskHandlerDefinitionMap::class) as $definition) {
                assert($definition instanceof TaskHandlerDefinition);
                $map[$definition->taskClass()] = $container->get($definition->serviceId());
            }
            return new TaskHandlerRegistry($map);
        });

        $container->register(NullHandler::class, function (Container $container) {
            return new NullHandler();
        }, [
            self::TAG_TASK_HANDLER => [
                'taskClass' => NullTask::class,
                'alias' => 'null',
            ]
        ]);

        $container->register(DebugTaskCommand::class, function (Container $container) {
            return new DebugTaskCommand(
                $container->get(TaskHandlerDefinitionMap::class)
            );
        }, [ ConsoleExtension::TAG_COMMAND => ['name' => 'debug:task']]);
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema)
    {
        $schema->setDefaults([
            self::PARAM_MILLISLEEP => 1,
            self::PARAM_CONCURRENCY => 10
        ]);
    }
}
