<?php

namespace Maestro\Extension\Git;

use Maestro\Extension\Vcs\VcsExtension;
use Maestro\Library\Script\ScriptRunner;
use Maestro\Library\Git\GitRepositoryFactory;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
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
