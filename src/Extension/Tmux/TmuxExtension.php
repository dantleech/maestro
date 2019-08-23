<?php

namespace Maestro\Extension\Tmux;

use Maestro\Extension\Tmux\Model\Command\TmuxCommand;
use Maestro\Extension\Tmux\Model\TmuxClient;
use Maestro\Workspace\WorkspaceFactory;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\Console\ConsoleExtension;
use Phpactor\MapResolver\Resolver;

class TmuxExtension implements Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        $container->register(TmuxCommand::class, function (Container $container) {
            return new TmuxCommand(
                $container->get(WorkspaceFactory::class),
                $container->get(TmuxClient::class)
            );
        }, [ ConsoleExtension::TAG_COMMAND => ['name' => 'tmux']]);

        $container->register(TmuxClient::class, function (Container $container) {
            return new TmuxClient();
        });
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema)
    {
    }
}
