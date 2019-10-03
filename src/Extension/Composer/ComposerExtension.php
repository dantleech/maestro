<?php

namespace Maestro\Extension\Composer;

use Amp\Artax\Client;
use Maestro\Library\Composer\Packagist;
use Maestro\Extension\Composer\Survery\ComposerConfigSurveryor;
use Maestro\Extension\Composer\Survery\PackagistSurveyor;
use Maestro\Extension\Survey\SurveyExtension;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\MapResolver\Resolver;

class ComposerExtension implements Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        $container->register(ComposerConfigSurveryor::class, function (Container $container) {
            return new ComposerConfigSurveryor();
        }, [ SurveyExtension::TAG_SURVERYOR => []]);

        //$container->register(PackagistSurveyor::class, function (Container $container) {
        //    return new PackagistSurveyor(
        //        $container->get(Packagist::class),
        //        $container->get(LoggingExtension::SERVICE_LOGGER)
        //    );
        //}, [ SurveyExtension::TAG_SURVERYOR => []]);

        $container->register(Packagist::class, function (Container $container) {
            return new Packagist($container->get(Client::class));
        });
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema)
    {
    }
}
