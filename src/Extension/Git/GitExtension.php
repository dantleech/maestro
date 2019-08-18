<?php

namespace Maestro\Extension\Git;

use Maestro\Extension\Git\Command\GitTagCommand;
use Maestro\Extension\Maestro\MaestroExtension;
use Maestro\Extension\Git\Task\GitHandler;
use Maestro\Extension\Git\Task\GitTask;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\Console\ConsoleExtension;
use Phpactor\MapResolver\Resolver;

class GitExtension implements Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        $container->register('git.command.tag', function (Container $container) {
            return new GitTagCommand(
                $container->get(MaestroExtension::SERVICE_CONSOLE_BEHAVIOR_GRAPH)
            );
        }, [ ConsoleExtension::TAG_COMMAND => [
            'name' => 'git:tag',
        ]]);

        $container->register('task.job_handler.git', function (Container $container) {
            return new GitHandler(
                $container->get('script.runner'),
                $container->getParameter(MaestroExtension::PARAM_WORKSPACE_DIRECTORY)
            );
        }, [ MaestroExtension::TAG_JOB_HANDLER => [
            'alias' => 'git',
            'job_class' => GitTask::class,
        ]]);
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema)
    {
    }
}
