<?php

namespace Maestro\Extension\Maestro;

use Maestro\Console\DumperRegistry;
use Maestro\Console\Logging\AnsiFormatter;
use Maestro\Extension\Maestro\Command\RunCommand;
use Maestro\Extension\Maestro\Dumper\DotDumper;
use Maestro\Extension\Maestro\Dumper\OverviewRenderer;
use Maestro\Extension\Maestro\Dumper\LeafArtifactsDumper;
use Maestro\Extension\Maestro\Dumper\TargetDumper;
use Maestro\Extension\Maestro\Task\GitHandler;
use Maestro\Extension\Maestro\Task\GitTask;
use Maestro\Extension\Maestro\Task\JsonFileHandler;
use Maestro\Extension\Maestro\Task\JsonFileTask;
use Maestro\Extension\Maestro\Task\ManifestHandler;
use Maestro\Extension\Maestro\Task\ManifestTask;
use Maestro\Extension\Maestro\Task\PackageHandler;
use Maestro\Extension\Maestro\Task\ScriptHandler;
use Maestro\Loader\Loader;
use Maestro\Loader\Processor\PrototypeExpandingProcessor;
use Maestro\MaestroBuilder;
use Maestro\Node\StateObserver\LoggingStateObserver;
use Maestro\Script\ScriptRunner;
use Maestro\Node\Task\NullHandler;
use Maestro\Node\Task\NullTask;
use Maestro\Extension\Maestro\Task\PackageTask;
use Maestro\Extension\Maestro\Task\ScriptTask;
use Maestro\Util\Cast;
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
    const SERVICE_RUNNER_BUILDER = 'runner_builder';
    const TAG_JOB_HANDLER = 'job_handler';

    const PARAM_WORKING_DIRECTORY = 'working_directory';
    const PARAM_WORKSPACE_DIRECTORY = 'workspace_directory';
    const PARAM_NAMESPACE = 'namespace';
    const SERVICE_MANIFEST_LOADER = 'config.loader';
    const TAG_DUMPER = 'dumper';
    const SERVICE_DUMPER_REGISTRY = 'dumper.registry';

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
        $container->register('workspace_factory', function (Container $container) {
            return new WorkspaceFactory(
                $container->getParameter('namespace'),
                $container->getParameter('workspace_directory')
            );
        });
    }

    private function loadConsole(ContainerBuilder $container)
    {
        $container->register('console.command.run', function (Container $container) {
            return new RunCommand(
                $container->get(self::SERVICE_RUNNER_BUILDER),
                $container->get(self::SERVICE_MANIFEST_LOADER),
                $container->get(self::SERVICE_DUMPER_REGISTRY)
            );
        }, [ ConsoleExtension::TAG_COMMAND => ['name' => 'run']]);
    }

    private function loadMaestro(ContainerBuilder $container)
    {
        $container->register(self::SERVICE_RUNNER_BUILDER, function (Container $container) {
            $builder = MaestroBuilder::create();
            $builder->addStateObserver(new LoggingStateObserver($container->get(LoggingExtension::SERVICE_LOGGER)));
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

        $container->register(self::SERVICE_MANIFEST_LOADER, function (Container $container) {
            return new Loader(Cast::toString(getcwd()), [
                new PrototypeExpandingProcessor(),
            ]);
        });

        $container->register('task.job_handler.null', function () {
            return new NullHandler();
        }, [ self::TAG_JOB_HANDLER => [
            'alias' => 'null',
            'job_class' => NullTask::class,
        ]]);

        $container->register('task.job_handler.manifest', function () {
            return new ManifestHandler();
        }, [ self::TAG_JOB_HANDLER => [
            'alias' => 'manifest',
            'job_class' => ManifestTask::class,
        ]]);

        $container->register('task.job_handler.package', function (Container $container) {
            return new PackageHandler($container->get('workspace_factory'));
        }, [ self::TAG_JOB_HANDLER => [
            'alias' => 'package',
            'job_class' => PackageTask::class,
        ]]);

        $container->register('task.job_handler.script', function (Container $container) {
            return new ScriptHandler($container->get('script.runner'));
        }, [ self::TAG_JOB_HANDLER => [
            'alias' => 'script',
            'job_class' => ScriptTask::class,
        ]]);

        $container->register('task.job_handler.git', function (Container $container) {
            return new GitHandler(
                $container->get('script.runner'),
                $container->getParameter(self::PARAM_WORKSPACE_DIRECTORY)
            );
        }, [ self::TAG_JOB_HANDLER => [
            'alias' => 'git',
            'job_class' => GitTask::class,
        ]]);

        $container->register('task.job_handler.json_file', function (Container $container) {
            return new JsonFileHandler();
        }, [ MaestroExtension::TAG_JOB_HANDLER => [
            'alias' => 'json_file',
            'job_class' => JsonFileTask::class,
        ]]);
    }

    private function loadScript(ContainerBuilder $container)
    {
        $container->register('script.runner', function (Container $container) {
            return new ScriptRunner($container->get(LoggingExtension::SERVICE_LOGGER));
        });
    }

    private function defaultNamespace()
    {
        return substr(md5($this->defaultCwd()), 0, 10);
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
        $container->register('logging.json', function (Container $container) {
            return new JsonFormatter();
        }, [ LoggingExtension::TAG_FORMATTER => ['alias' => 'json']]);

        $container->register('logging.ansi', function (Container $container) {
            return new AnsiFormatter();
        }, [ LoggingExtension::TAG_FORMATTER => ['alias' => 'ansi']]);
    }

    private function loadDumpers(ContainerBuilder $container): void
    {
        $container->register(self::SERVICE_DUMPER_REGISTRY, function (Container $container) {
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

        $container->register('dumper.dot', function (Container $container) {
            return new DotDumper();
        }, [ self::TAG_DUMPER => [ 'name' => 'dot' ] ]);

        $container->register('dumper.overview', function (Container $container) {
            return new OverviewRenderer();
        }, [ self::TAG_DUMPER => [ 'name' => 'overview' ] ]);

        $container->register('dumper.artifacts', function (Container $container) {
            return new LeafArtifactsDumper();
        }, [ self::TAG_DUMPER => [ 'name' => 'artifacts' ] ]);

        $container->register('dumper.targets', function (Container $container) {
            return new TargetDumper();
        }, [ self::TAG_DUMPER => [ 'name' => 'targets' ] ]);
    }
}
