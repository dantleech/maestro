<?php

namespace Maestro\Console;

use Maestro\Extension\Maestro\MaestroExtension;
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
        $container = $this->buildContainer($definition);
        $application->setCommandLoader(
            $container->get(ConsoleExtension::SERVICE_COMMAND_LOADER)
        );
        $container->get(LoggingExtension::SERVICE_LOGGER)->info(
            sprintf('Built application in %s', microtime(true) - $start)
        );

        return $application;
    }

    private function defineGlobalOptions(Application $application): InputDefinition
    {
        $definition = $application->getDefinition();
        $definition->addOptions([
            new InputOption(self::OPTION_LOGGING_ENABLED, null, InputOption::VALUE_NONE, 'Enable logging'),
            new InputOption(self::OPTION_LOG_PATH, null, InputOption::VALUE_REQUIRED, 'File to log to', 'maestro.json'),
            new InputOption(self::OPTION_LOG_LEVEL, null, InputOption::VALUE_REQUIRED, 'Log level', 'debug'),
            new InputOption(self::OPTION_WORKSPACE_DIRECTORY, null, InputOption::VALUE_REQUIRED, 'Path to workspace'),
            new InputOption(self::OPTION_WORKING_DIRECTORY, null, InputOption::VALUE_REQUIRED, 'Working directory'),
            new InputOption(self::OPTION_NAMESPACE, null, InputOption::VALUE_REQUIRED, 'Namepace (defaults to value based on cwd)'),
        ]);
        return $definition;
    }

    private function buildContainer($definition): Container
    {
        return PhpactorContainer::fromExtensions([
            ConsoleExtension::class,
            MaestroExtension::class,
            LoggingExtension::class,
        ], $this->buildConfiguration($definition));
    }

    private function buildConfiguration(InputDefinition $definition): array
    {
        $config = [
            LoggingExtension::PARAM_LEVEL => 'debug',
            LoggingExtension::PARAM_PATH => 'maestro.log',
        ];

        foreach ([
            LoggingExtension::PARAM_ENABLED => self::OPTION_LOGGING_ENABLED,
            LoggingExtension::PARAM_LEVEL => self::OPTION_LOG_LEVEL,
            LoggingExtension::PARAM_PATH => self::OPTION_LOG_PATH,
            MaestroExtension::PARAM_WORKING_DIRECTORY => self::OPTION_WORKING_DIRECTORY,
            MaestroExtension::PARAM_WORKSPACE_DIRECTORY => self::OPTION_WORKSPACE_DIRECTORY,
            MaestroExtension::PARAM_NAMESPACE => self::OPTION_NAMESPACE
            ,
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
}
