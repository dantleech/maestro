<?php

namespace Maestro;

use Maestro\Extension\Composer\ComposerExtension;
use Maestro\Extension\File\FileExtension;
use Maestro\Extension\Git\GitExtension;
use Maestro\Extension\Report\ReportExtension;
use Maestro\Extension\Runner\RunnerExtension;
use Maestro\Extension\Script\ScriptExtension;
use Maestro\Extension\Survey\SurveyExtension;
use Maestro\Extension\Task\TaskExtension;
use Maestro\Extension\Template\TemplateExtension;
use Maestro\Extension\Vcs\VcsExtension;
use Maestro\Extension\Workspace\WorkspaceExtension;
use Phpactor\Container\Container;
use Phpactor\Container\PhpactorContainer;
use Phpactor\Extension\Console\ConsoleExtension;
use Phpactor\Extension\Logger\LoggingExtension;
use RuntimeException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Webmozart\PathUtil\Path;
use XdgBaseDir\Xdg;
use function Safe\getcwd;

final class ApplicationBuilder
{
    private const OPTION_LOGGING_ENABLED = 'log-enable';
    private const OPTION_LOG_PATH = 'log-path';
    private const OPTION_LOG_LEVEL = 'log-level';
    private const OPTION_LOG_FORMAT = 'log-format';
    private const OPTION_CONCURRENCY = 'concurrency';
    private const OPTION_WORKSPACE_PATH = 'workspace-dir';
    private const OPTION_WORKING_DIRECTORY = 'working-dir';
    private const OPTION_WORKSPACE_NAMESPACE = 'namespace';
    private const OPTION_MANIFEST_PATH = 'plan';
    private const OPTION_PURGE = 'purge';

    /**
     * @var InputInterface
     */
    private $input;

    public function __construct(?InputInterface $input = null)
    {
        $this->input = $input ?: new ArgvInput();
    }

    public function build(): Application
    {
        $start = microtime(true);
        $this->setWorkingDirectory();
        $application = new Application();
        $definition = $this->defineGlobalOptions($application);
        $container = $this->buildContainer($this->buildConfiguration($definition));
        $application->setCommandLoader(
            $container->get(ConsoleExtension::SERVICE_COMMAND_LOADER)
        );
        $container->get(LoggingExtension::SERVICE_LOGGER)->info(
            sprintf('Built application in %s', microtime(true) - $start)
        );

        return $application;
    }

    public function buildContainer(array $config): Container
    {
        return PhpactorContainer::fromExtensions([
            ConsoleExtension::class,
            LoggingExtension::class,
            RunnerExtension::class,
            ScriptExtension::class,
            TemplateExtension::class,
            TaskExtension::class,
            WorkspaceExtension::class,
            VcsExtension::class,
            GitExtension::class,
            FileExtension::class,
            SurveyExtension::class,
            ReportExtension::class,
            ComposerExtension::class,
        ], $config);
    }

    private function defineGlobalOptions(Application $application): InputDefinition
    {
        $definition = $application->getDefinition();
        $definition->addOptions([
            new InputOption(self::OPTION_LOGGING_ENABLED, null, InputOption::VALUE_NONE, 'Enable logging'),
            new InputOption(self::OPTION_LOG_PATH, null, InputOption::VALUE_REQUIRED, 'File to log to', 'maestro.json'),
            new InputOption(self::OPTION_LOG_FORMAT, null, InputOption::VALUE_REQUIRED, 'Log format', ''),
            new InputOption(self::OPTION_LOG_LEVEL, null, InputOption::VALUE_REQUIRED, 'Log level', 'warning'),
            new InputOption(self::OPTION_WORKSPACE_PATH, null, InputOption::VALUE_REQUIRED, 'Path to workspace'),
            new InputOption(self::OPTION_WORKING_DIRECTORY, null, InputOption::VALUE_REQUIRED, 'Working directory'),
            new InputOption(self::OPTION_WORKSPACE_NAMESPACE, null, InputOption::VALUE_REQUIRED, 'Namepace (defaults to value based on cwd)'),
            new InputOption(self::OPTION_MANIFEST_PATH, null, InputOption::VALUE_REQUIRED, 'Path to manifest (plan) defaults to maestro.json'),
            new InputOption(self::OPTION_PURGE, null, InputOption::VALUE_NONE, 'Purge workspace before starting'),
            new InputOption(self::OPTION_CONCURRENCY, null, InputOption::VALUE_REQUIRED, 'Set worker job concurrency'),
        ]);
        return $definition;
    }

    private function buildConfiguration(InputDefinition $definition): array
    {
        $config = [
            LoggingExtension::PARAM_LEVEL => 'warning',
            LoggingExtension::PARAM_PATH => STDERR,
            LoggingExtension::PARAM_FORMATTER => 'console',
            RunnerExtension::PARAM_MANIFEST_PATH => getcwd() . '/maestro.json',
            RunnerExtension::PARAM_PURGE => false,
            TaskExtension::PARAM_CONCURRENCY => 10,
            WorkspaceExtension::PARAM_WORKSPACE_PATH => Path::join([(new Xdg())->getHomeDataDir(), 'maestro']),
            WorkspaceExtension::PARAM_WORKSPACE_NAMESPACE => md5(getcwd()),
        ];

        foreach ([
            LoggingExtension::PARAM_ENABLED => self::OPTION_LOGGING_ENABLED,
            LoggingExtension::PARAM_LEVEL => self::OPTION_LOG_LEVEL,
            LoggingExtension::PARAM_PATH => self::OPTION_LOG_PATH,
            LoggingExtension::PARAM_FORMATTER => self::OPTION_LOG_FORMAT,
            RunnerExtension::PARAM_WORKING_DIRECTORY => self::OPTION_WORKING_DIRECTORY,
            RunnerExtension::PARAM_MANIFEST_PATH => self::OPTION_MANIFEST_PATH,
            RunnerExtension::PARAM_PURGE => self::OPTION_PURGE,
            TaskExtension::PARAM_CONCURRENCY => self::OPTION_CONCURRENCY,
            WorkspaceExtension::PARAM_WORKSPACE_PATH => self::OPTION_WORKSPACE_PATH,
            WorkspaceExtension::PARAM_WORKSPACE_NAMESPACE => self::OPTION_WORKSPACE_NAMESPACE,
        ] as $configKey => $optionName) {
            $option = $definition->getOption($optionName);
            $optionName = '--' . $optionName;

            if (!$this->input->hasParameterOption($optionName)) {
                continue;
            }

            $value = $this->input->getParameterOption($optionName);
            if (false === $option->acceptValue()) {
                $value = true;
            }

            $config[$configKey] = $value;
        }

        $config = $this->configureMetaStates($config);

        return $config;
    }

    private function setWorkingDirectory()
    {
        $optionName = '--' . self::OPTION_WORKING_DIRECTORY;

        if (false === $this->input->hasParameterOption($optionName)) {
            return;
        }

        $path = $this->input->getParameterOption($optionName);

        if (false === file_exists($path)) {
            throw new RuntimeException(sprintf(
                'Working directory "%s" does not exist',
                $path
            ));
        }

        if (true === chdir($path)) {
            return;
        }

        throw new RuntimeException(sprintf(
            'Could not change directory to "%s"',
            $path
        ));
    }

    private function configureMetaStates(array $config)
    {
        if ($this->input->hasParameterOption(['-v','--verbose'])) {
            $config[LoggingExtension::PARAM_ENABLED] = true;
            $config[LoggingExtension::PARAM_LEVEL] = 'info';
        }

        if ($this->input->hasParameterOption(['-vv','-vvv'])) {
            $config[LoggingExtension::PARAM_LEVEL] = 'debug';
        }

        return $config;
    }
}
