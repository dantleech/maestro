<?php

namespace Maestro\Extension\Git;

use Maestro\Extension\Git\Command\TagVersionCommand;
use Maestro\Extension\Vcs\VcsExtension;
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
use Maestro\Library\Script\ScriptRunner;
use Maestro\Tests\Unit\Library\Git\GitRepositoryFactory;
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
        $container->register(GitRepositoryFactory::class, function (Container $container) {
            return new GitRepositoryFactory(
                $container->get(ScriptRunner::class),
                $container->get(LoggingExtension::SERVICE_LOGGER)
            );
        }, [
            VcsExtension::TAG_REPOSITORY_FACTORY => [
                'type' => 'git'
            ]
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema)
    {
    }
}
