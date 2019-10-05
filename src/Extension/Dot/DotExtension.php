<?php

namespace Maestro\Extension\Dot;

use Maestro\Extension\Dot\Report\DotReport;
use Maestro\Extension\Report\ReportExtension;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\MapResolver\Resolver;

class DotExtension implements Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        $container->register(DotReport::class, function () {
            return new DotReport();
        }, [
            ReportExtension::TAG_REPORT_CONSOLE => [
                'name' => 'dot'
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
