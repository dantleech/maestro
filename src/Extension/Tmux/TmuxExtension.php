<?php

namespace Maestro\Extension\Tmux;

use Maestro\Extension\Maestro\MaestroExtension;
use Maestro\Extension\Tmux\Model\Command\TmuxCommand;
use Maestro\Extension\Tmux\Model\TmuxClient;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\Console\ConsoleExtension;
use Phpactor\MapResolver\Resolver;

class TmuxExtension implements Extension
{
    const SERVICE_TMUX_CLIENT = 'tmux.client';

    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        $container->register('tmux.command.tmux', function (Container $container) {
            return new TmuxCommand(
                $container->get(MaestroExtension::SERVICE_WORKSPACE_FACTORY),
                $container->get(self::SERVICE_TMUX_CLIENT)
            );
        }, [ ConsoleExtension::TAG_COMMAND => ['name' => 'tmux']]);

        $container->register('tmux.client', function (Container $container) {
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
