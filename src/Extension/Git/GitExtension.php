<?php

namespace Maestro\Extension\Git;

use Maestro\Extension\Git\Command\TagVersionCommand;
use Maestro\Extension\Git\Model\Git;
use Maestro\Extension\Git\Task\TagVersionHandler;
use Maestro\Extension\Git\Task\TagVersionTask;
use Maestro\Extension\Git\Task\VersionInfoHandler;
use Maestro\Extension\Git\Task\VersionInfoTask;
use Maestro\Extension\Maestro\Command\Behavior\GraphBehavior;
use Maestro\Extension\Maestro\MaestroExtension;
use Maestro\Extension\Git\Task\GitHandler;
use Maestro\Extension\Git\Task\GitTask;
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
                $container->get(GraphBehavior::class)
            );
        }, [ ConsoleExtension::TAG_COMMAND => [
            'name' => 'git:tag',
        ]]);

        $container->register(Git::class, function (Container $container) {
            return new Git(
                $container->get(ScriptRunner::class),
                $container->get(LoggingExtension::SERVICE_LOGGER)
            );
        });

        $container->register(GitHandler::class, function (Container $container) {
            return new GitHandler(
                $container->get(ScriptRunner::class),
                $container->getParameter(MaestroExtension::PARAM_WORKSPACE_DIRECTORY)
            );
        }, [ MaestroExtension::TAG_JOB_HANDLER => [
            'alias' => 'git',
            'job_class' => GitTask::class,
        ]]);

        $container->register(TagVersionHandler::class, function (Container $container) {
            return new TagVersionHandler(
                $container->get(Git::class),
                $container->get(LoggingExtension::SERVICE_LOGGER)
            );
        }, [ MaestroExtension::TAG_JOB_HANDLER => [
            'alias' => 'git_tag',
            'job_class' => TagVersionTask::class,
        ]]);

        $container->register(VersionInfoHandler::class, function (Container $container) {
            return new VersionInfoHandler(
                $container->get(Git::class)
            );
        }, [ MaestroExtension::TAG_JOB_HANDLER => [
            'alias' => 'git_version_info',
            'job_class' => VersionInfoTask::class,
        ]]);
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema)
    {
    }
}
