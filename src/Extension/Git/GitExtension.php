<?php

namespace Maestro\Extension\Git;

use Maestro\Extension\Git\Command\TagVersionCommand;
use Maestro\Library\Git\GitRepository;
use Maestro\Extension\Git\Survey\VersionSurveyor;
use Maestro\Extension\Git\Task\TagVersionHandler;
use Maestro\Extension\Git\Task\TagVersionTask;
use Maestro\Extension\Maestro\Command\Behavior\GraphBehavior;
use Maestro\Extension\Maestro\MaestroExtension;
use Maestro\Extension\Git\Task\GitHandler;
use Maestro\Extension\Git\Task\GitTask;
use Maestro\Extension\Version\Console\VersionReport;
use Maestro\Extension\Survey\SurveyExtension;
use Maestro\Script\ScriptRunner;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\Console\ConsoleExtension;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\MapResolver\Resolver;

class GitExtension implements Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        $container->register(TagVersionCommand::class, function (Container $container) {
            return new TagVersionCommand(
                $container->get(GraphBehavior::class),
                $container->get(VersionReport::class)
            );
        }, [ ConsoleExtension::TAG_COMMAND => [
            'name' => 'git:tag',
        ]]);

        $container->register(GitRepository::class, function (Container $container) {
            return new GitRepository(
                $container->get(ScriptRunner::class),
                $container->get(LoggingExtension::SERVICE_LOGGER)
            );
        });

        $container->register(GitHandler::class, function (Container $container) {
            return new GitHandler(
                $container->get(ScriptRunner::class),
                $container->getParameter(MaestroExtension::PARAM_WORKSPACE_DIRECTORY)
            );
        }, [ MaestroExtension::TAG_TASK_HANDLER => [
            'alias' => 'git',
            'taskClass' => GitTask::class,
        ]]);

        $container->register(VersionSurveyor::class, function (Container $container) {
            return new VersionSurveyor(
                $container->get(GitRepository::class)
            );
        }, [ SurveyExtension::TAG_SURVERYOR => []]);

        $container->register(TagVersionHandler::class, function (Container $container) {
            return new TagVersionHandler(
                $container->get(GitRepository::class),
                $container->get(LoggingExtension::SERVICE_LOGGER)
            );
        }, [ MaestroExtension::TAG_TASK_HANDLER => [
            'alias' => 'git_tag',
            'taskClass' => TagVersionTask::class,
        ]]);
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema)
    {
    }
}
