<?php

namespace Maestro;

use Maestro\Extension\Runner\RunnerExtension;
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

final class ApplicationBuilder
{
    private const OPTION_LOGGING_ENABLED = 'log-enable';
    private const OPTION_LOG_PATH = 'log-path';
    private const OPTION_LOG_LEVEL = 'log-level';
    private const OPTION_LOG_FORMAT = 'log-format';
    private const OPTION_WORKSPACE_DIRECTORY = 'workspace-dir';
    private const OPTION_WORKING_DIRECTORY = 'working-dir';
    private const OPTION_NAMESPACE = 'namespace';

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
            new InputOption(self::OPTION_WORKSPACE_DIRECTORY, null, InputOption::VALUE_REQUIRED, 'Path to workspace'),
            new InputOption(self::OPTION_WORKING_DIRECTORY, null, InputOption::VALUE_REQUIRED, 'Working directory'),
            new InputOption(self::OPTION_NAMESPACE, null, InputOption::VALUE_REQUIRED, 'Namepace (defaults to value based on cwd)'),
        ]);
        return $definition;
    }

    private function buildConfiguration(InputDefinition $definition): array
    {
        $config = [
            LoggingExtension::PARAM_LEVEL => 'warning',
            LoggingExtension::PARAM_PATH => STDERR,
            LoggingExtension::PARAM_FORMATTER => null,
        ];

        foreach ([
            LoggingExtension::PARAM_ENABLED => self::OPTION_LOGGING_ENABLED,
            LoggingExtension::PARAM_LEVEL => self::OPTION_LOG_LEVEL,
            LoggingExtension::PARAM_PATH => self::OPTION_LOG_PATH,
            LoggingExtension::PARAM_FORMATTER => self::OPTION_LOG_FORMAT,
            RunnerExtension::PARAM_WORKING_DIRECTORY => self::OPTION_WORKING_DIRECTORY,
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
