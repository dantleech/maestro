<?php

namespace Maestro\Extension\Composer;

use Amp\Artax\Client;
use Maestro\Extension\Composer\Model\Packagist;
use Maestro\Extension\Composer\Survery\ComposerSurveryor;
use Maestro\Extension\Composer\Survery\PackagistSurveyor;
use Maestro\Extension\Survey\SurveyExtension;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\MapResolver\Resolver;

class ComposerExtension implements Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        $container->register(ComposerSurveryor::class, function (Container $container) {
            return new ComposerSurveryor();
        }, [ SurveyExtension::TAG_SURVERYOR => []]);

        $container->register(PackagistSurveyor::class, function (Container $container) {
            return new PackagistSurveyor($container->get(Packagist::class));
        }, [ SurveyExtension::TAG_SURVERYOR => []]);

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
