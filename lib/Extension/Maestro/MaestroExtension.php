<?php

namespace Maestro\Extension\Maestro;

use Maestro\Extension\Maestro\Command\RunCommand;
use Maestro\RunnerBuilder;
use Maestro\Task\Task\NullHandler;
use Maestro\Task\Task\NullTask;
use Maestro\Task\Task\PackageTask;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\Console\ConsoleExtension;
use Phpactor\MapResolver\Resolver;

class MaestroExtension implements Extension
{
    const SERVICE_RUNNER_BUILDER = 'runner_builder';

    public function load(ContainerBuilder $container)
    {
        $this->loadConsole($container);
        $this->loadMaestro($container);
    }

    public function configure(Resolver $schema)
    {
    }

    private function loadConsole(ContainerBuilder $container)
    {
        $container->register('console.command.run', function (Container $container) {
            return new RunCommand($container->get(self::SERVICE_RUNNER_BUILDER));
        }, [ ConsoleExtension::TAG_COMMAND => ['name' => 'run']]);
    }

    private function loadMaestro(ContainerBuilder $container)
    {
        $container->register(self::SERVICE_RUNNER_BUILDER, function (Container $container) {
            return RunnerBuilder::create()
                ->addJobHandler('null', NullTask::class, new NullHandler())
                ->addJobHandler('package', PackageTask::class, new NullHandler());
        });
    }
}
