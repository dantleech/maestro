<?php

namespace Maestro\Extension\Maestro;

use Maestro\Extension\Maestro\Command\RunCommand;
use Maestro\Extension\Maestro\Task\PackageHandler;
use Maestro\Extension\Maestro\Task\ScriptHandler;
use Maestro\RunnerBuilder;
use Maestro\Task\Task\NullHandler;
use Maestro\Task\Task\NullTask;
use Maestro\Task\Task\PackageTask;
use Maestro\Task\Task\ScriptTask;
use Maestro\Workspace\WorkspaceFactory;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\Console\ConsoleExtension;
use Phpactor\MapResolver\Resolver;
use RuntimeException;
use Webmozart\PathUtil\Path;
use XdgBaseDir\Xdg;

class MaestroExtension implements Extension
{
    const SERVICE_RUNNER_BUILDER = 'runner_builder';
    const TAG_JOB_HANDLER = 'job_handler';

    public function configure(Resolver $schema)
    {
        $schema->setDefaults([
            'working_directory' => getcwd()
        ]);
    }

    public function load(ContainerBuilder $container)
    {
        $this->loadWorkspace($container);
        $this->loadConsole($container);
        $this->loadMaestro($container);
    }

    private function loadWorkspace(ContainerBuilder $container)
    {
        $container->register('workspace_factory', function (Container $container) {
            return new WorkspaceFactory(
                substr(md5($container->getParameter('working_directory')), 0, 10),
                Path::join([(new Xdg())->getHomeDataDir(), 'maestro'])
            );
        });
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
            $builder = RunnerBuilder::create();
            foreach ($container->getServiceIdsForTag('job_handler') as $serviceId => $attrs) {
                if (!isset($attrs['alias'])) {
                    throw new RuntimeException(sprintf(
                        'Job handler "%s" must specify an alias',
                        $serviceId
                    ));
                }
                if (!isset($attrs['job_class'])) {
                    throw new RuntimeException(sprintf(
                        'Job handler "%s" must specify a job class',
                        $serviceId
                    ));
                }

                $builder->addJobHandler($attrs['alias'], $attrs['job_class'], $container->get($serviceId));
            }

            return $builder;
        });

        $container->register('task.job_handler.null', function () {
            return new NullHandler();
        }, [ self::TAG_JOB_HANDLER => [
            'alias' => 'null',
            'job_class' => NullTask::class,
        ]]);

        $container->register('task.job_handler.package', function (Container $container) {
            return new PackageHandler($container->get('workspace_factory'));
        }, [ self::TAG_JOB_HANDLER => [
            'alias' => 'package',
            'job_class' => PackageTask::class,
        ]]);

        $container->register('task.job_handler.script', function (Container $container) {
            return new ScriptHandler();
        }, [ self::TAG_JOB_HANDLER => [
            'alias' => 'script',
            'job_class' => ScriptTask::class,
        ]]);
    }
}
