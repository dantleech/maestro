<?php

namespace Maestro\Extension\Git;

use Maestro\Extension\Git\Command\TagVersionCommand;
use Maestro\Extension\Git\Model\Git;
use Maestro\Extension\Git\Survey\VersionSurveyor;
use Maestro\Extension\Maestro\Command\Behavior\GraphBehavior;
use Maestro\Extension\Maestro\MaestroExtension;
use Maestro\Extension\Git\Task\GitHandler;
use Maestro\Extension\Git\Task\GitTask;
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

        $container->register(VersionSurveyor::class, function (Container $container) {
            return new VersionSurveyor(
                $container->get(Git::class)
            );
        }, [ SurveyExtension::TAG_SURVERYOR => []]);
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema)
    {
    }
}
