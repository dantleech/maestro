<?php

namespace Maestro\Extension\Runner;

use Maestro\Extension\Runner\Command\Behavior\GraphBehavior;
use Maestro\Extension\Runner\Command\RunCommand;
use Maestro\Extension\Runner\Loader\AliasToClassMap;
use Maestro\Extension\Runner\Loader\GraphConstructor;
use Maestro\Extension\Runner\Loader\ManifestLoader;
use Maestro\Extension\Runner\Loader\Processor\PrototypeExpandingProcessor;
use Maestro\Extension\Runner\Loader\Processor\TaskAliasExpandingProcessor;
use Maestro\Extension\Runner\Report\RunReport;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\Console\ConsoleExtension;
use Phpactor\MapResolver\Resolver;

class RunnerExtension implements Extension
{
    const PARAM_WORKING_DIRECTORY = 'runner.working-directory';
    const PARAM_PURGE = 'runner.purge';
    const SERVICE_TASK_ALIAS_TO_CLASS_MAP = 'runner.alias_to_class_map';

    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        $this->registerConsole($container);
        $this->registerLoader($container);
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
                        $container->get(self::SERVICE_TASK_ALIAS_TO_CLASS_MAP),
                    )
                ]
            );
        });

        $container->register(self::SERVICE_TASK_ALIAS_TO_CLASS_MAP, function () {
            return new AliasToClassMap('task', []);
        });

        $container->register(GraphConstructor::class, function (Container $container) {
            return new GraphConstructor($container->getParameter(self::PARAM_PURGE));
        });
    }
}
