<?php

namespace Maestro\Extension\Composer;

use Maestro\Extension\Composer\Survery\ComposerSurveryor;
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
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema)
    {
    }
}
