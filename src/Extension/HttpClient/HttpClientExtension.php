<?php

namespace Maestro\Extension\HttpClient;

use Amp\Artax\Client;
use Amp\Artax\DefaultClient;
use Maestro\Extension\HttpClient\Client\LoggingClient;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\MapResolver\Resolver;

class HttpClientExtension implements Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        $container->register(Client::class, function (Container $container) {
            return new LoggingClient(
                new DefaultClient(),
                $container->get(LoggingExtension::SERVICE_LOGGER)
            );
        });
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema)
    {
    }
}
