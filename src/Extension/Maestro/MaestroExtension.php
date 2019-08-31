<?php

namespace Maestro\Extension\Maestro;

use Maestro\Console\DumperRegistry;
use Maestro\Console\Logging\AnsiFormatter;
use Maestro\Console\Report\RunReport;
use Maestro\Extension\Maestro\Command\Behavior\GraphBehavior;
use Maestro\Extension\Maestro\Command\RunCommand;
use Maestro\Extension\Maestro\Console\TagParser;
use Maestro\Extension\Maestro\Dumper\DotDumper;
use Maestro\Extension\Maestro\Dumper\LeafArtifactsDumper;
use Maestro\Extension\Maestro\Dumper\OverviewRenderer;
use Maestro\Extension\Maestro\Dumper\TargetDumper;
use Maestro\Extension\Maestro\Task\JsonFileHandler;
use Maestro\Extension\Maestro\Task\JsonFileTask;
use Maestro\Extension\Maestro\Task\ManifestHandler;
use Maestro\Extension\Maestro\Task\ManifestTask;
use Maestro\Extension\Maestro\Task\PackageHandler;
use Maestro\Extension\Maestro\Task\ScriptHandler;
use Maestro\MaestroBuilder;
use Maestro\Graph\Scheduler\AsapSchedule;
use Maestro\Graph\Scheduler\AsapScheduler;
use Maestro\Graph\Scheduler\RepeatSchedule;
use Maestro\Graph\Scheduler\RepeatScheduler;
use Maestro\Extension\Maestro\StateObserver\LoggingStateObserver;
use Maestro\Script\ScriptRunner;
use Maestro\Graph\Task\NullHandler;
use Maestro\Graph\Task\NullTask;
use Maestro\Extension\Maestro\Task\PackageTask;
use Maestro\Extension\Maestro\Task\ScriptTask;
use Maestro\Workspace\PathStrategy\NestedDirectoryStrategy;
use Maestro\Workspace\WorkspaceFactory;
use Monolog\Formatter\JsonFormatter;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\Console\ConsoleExtension;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\MapResolver\Resolver;
use RuntimeException;
use Webmozart\PathUtil\Path;
use XdgBaseDir\Xdg;

class MaestroExtension implements Extension
{
    const TAG_TASK_HANDLER = 'task_handler';

    const PARAM_WORKING_DIRECTORY = 'working_directory';
    const PARAM_WORKSPACE_DIRECTORY = 'workspace_directory';
    const PARAM_NAMESPACE = 'namespace';
    const TAG_DUMPER = 'dumper';
    const TAG_SCHEDULER = 'scheduler';

    public function configure(Resolver $schema)
    {
        $schema->setDefaults([
            self::PARAM_WORKING_DIRECTORY => $this->defaultCwd(),
            self::PARAM_WORKSPACE_DIRECTORY => Path::join([(new Xdg())->getHomeDataDir(), 'maestro']),
            self::PARAM_NAMESPACE => $this->defaultNamespace(),
        ]);
    }

    public function load(ContainerBuilder $container)
    {
        $this->loadWorkspace($container);
        $this->loadConsole($container);
        $this->loadMaestro($container);
        $this->loadScript($container);
        $this->loadLogging($container);
        $this->loadDumpers($container);
    }

    private function loadWorkspace(ContainerBuilder $container)
    {
        $container->register(WorkspaceFactory::class, function (Container $container) {
            return new WorkspaceFactory(
                new NestedDirectoryStrategy(),
                $container->getParameter('namespace'),
                $container->getParameter('workspace_directory')
            );
        });
    }

    private function loadConsole(ContainerBuilder $container)
    {
        $container->register(RunCommand::class, function (Container $container) {
            return new RunCommand(
                $container->get(GraphBehavior::class),
                $container->get(DumperRegistry::class),
                $container->get(RunReport::class),
            );
        }, [ ConsoleExtension::TAG_COMMAND => ['name' => 'run']]);

        $container->register(GraphBehavior::class, function (Container $container) {
            return new GraphBehavior(
                $container->get(MaestroBuilder::class),
                $container->get(TagParser::class)
            );
        });

        $container->register(TagParser::class, function () {
            return new TagParser();
        });

        $container->register(RunReport::class, function (Container $container) {
            return new RunReport();
        });
    }

    private function loadMaestro(ContainerBuilder $container)
    {
        $container->register(MaestroBuilder::class, function (Container $container) {
            $builder = MaestroBuilder::create();
            $builder->addStateObserver(new LoggingStateObserver($container->get(LoggingExtension::SERVICE_LOGGER)));
            foreach ($container->getServiceIdsForTag('task_handler') as $serviceId => $attrs) {
                if (!isset($attrs['alias'])) {
                    throw new RuntimeException(sprintf(
                        'Task handler "%s" must specify an alias',
                        $serviceId
                    ));
                }
                if (!isset($attrs['task_class'])) {
                    throw new RuntimeException(sprintf(
                        'Task handler "%s" must specify a task class',
                        $serviceId
                    ));
                }

                $builder->addTaskHandler($attrs['alias'], $attrs['task_class'], $container->get($serviceId));
            }

            foreach ($container->getServiceIdsForTag(self::TAG_SCHEDULER) as $serviceId => $attrs) {
                if (!isset($attrs['alias'])) {
                    throw new RuntimeException(sprintf(
                        'Scheduler "%s" must specify an alias',
                        $serviceId
                    ));
                }
                if (!isset($attrs['schedule_class'])) {
                    throw new RuntimeException(sprintf(
                        'Scheduler "%s" must specify a schedule class',
                        $serviceId
                    ));
                }

                $builder->addSchedule(
                    $attrs['alias'],
                    $attrs['schedule_class'],
                    $container->get($serviceId)
                );
            }

            return $builder;
        });

        $this->loadTaskHandlers($container);
        $this->loadSchedulers($container);
    }

    private function loadScript(ContainerBuilder $container)
    {
        $container->register(ScriptRunner::class, function (Container $container) {
            return new ScriptRunner($container->get(LoggingExtension::SERVICE_LOGGER));
        });
    }

    private function defaultNamespace(): string
    {
        return basename($this->defaultCwd()) . '-' . substr(md5($this->defaultCwd()), 0, 10);
    }

    private function defaultCwd(): string
    {
        $cwd = getcwd();

        if (false === $cwd) {
            throw new RuntimeException(
                'Could not determine cwd'
            );
        }

        return $cwd;
    }

    private function loadLogging(ContainerBuilder $container): void
    {
        $container->register(JsonFormatter::class, function (Container $container) {
            return new JsonFormatter();
        }, [ LoggingExtension::TAG_FORMATTER => ['alias' => 'json']]);

        $container->register(AnsiFormatter::class, function (Container $container) {
            return new AnsiFormatter();
        }, [ LoggingExtension::TAG_FORMATTER => ['alias' => 'ansi']]);
    }

    private function loadDumpers(ContainerBuilder $container): void
    {
        $container->register(DumperRegistry::class, function (Container $container) {
            $dumpers = [];
            foreach ($container->getServiceIdsForTag(self::TAG_DUMPER) as $serviceId => $attrs) {
                if (!isset($attrs['name'])) {
                    throw new RuntimeException(sprintf(
                        'Dumper definition "%s" must include the `name` attribute',
                        $serviceId
                    ));
                }

                $dumpers[$attrs['name']] = $container->get($serviceId);
            }

            return new DumperRegistry($dumpers);
        });

        $container->register(DotDumper::class, function (Container $container) {
            return new DotDumper();
        }, [ self::TAG_DUMPER => [ 'name' => 'dot' ] ]);

        $container->register(OverviewRenderer::class, function (Container $container) {
            return new OverviewRenderer();
        }, [ self::TAG_DUMPER => [ 'name' => 'overview' ] ]);

        $container->register(LeafArtifactsDumper::class, function (Container $container) {
            return new LeafArtifactsDumper();
        }, [ self::TAG_DUMPER => [ 'name' => 'environment' ] ]);

        $container->register(TargetDumper::class, function (Container $container) {
            return new TargetDumper();
        }, [ self::TAG_DUMPER => [ 'name' => 'targets' ] ]);
    }

    private function loadTaskHandlers(ContainerBuilder $container)
    {
        $container->register(NullHandler::class, function () {
            return new NullHandler();
        }, [ self::TAG_TASK_HANDLER => [
            'alias' => 'null',
            'task_class' => NullTask::class,
        ]]);
        
        $container->register(ManifestHandler::class, function () {
            return new ManifestHandler();
        }, [ self::TAG_TASK_HANDLER => [
            'alias' => 'manifest',
            'task_class' => ManifestTask::class,
        ]]);
        
        $container->register(PackageHandler::class, function (Container $container) {
            return new PackageHandler($container->get(WorkspaceFactory::class));
        }, [ self::TAG_TASK_HANDLER => [
            'alias' => 'package',
            'task_class' => PackageTask::class,
        ]]);
        
        $container->register(ScriptHandler::class, function (Container $container) {
            return new ScriptHandler($container->get(ScriptRunner::class));
        }, [ self::TAG_TASK_HANDLER => [
            'alias' => 'script',
            'task_class' => ScriptTask::class,
        ]]);
        
        $container->register(JsonFileHandler::class, function (Container $container) {
            return new JsonFileHandler();
        }, [ MaestroExtension::TAG_TASK_HANDLER => [
            'alias' => 'json_file',
            'task_class' => JsonFileTask::class,
        ]]);
    }

    private function loadSchedulers(ContainerBuilder $container)
    {
        $container->register(AsapScheduler::class, function (Container $container) {
            return new AsapScheduler();
        }, [ MaestroExtension::TAG_SCHEDULER => [
            'alias' => 'asap',
            'schedule_class' => AsapSchedule::class,
        ]]);

        $container->register(RepeatScheduler::class, function (Container $container) {
            return new RepeatScheduler();
        }, [ MaestroExtension::TAG_SCHEDULER => [
            'alias' => 'repeat',
            'schedule_class' => RepeatSchedule::class,
        ]]);
    }
}
