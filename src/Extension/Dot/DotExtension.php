<?php

namespace Maestro\Extension\Dot;

use Maestro\Extension\Dot\Report\DotReport;
use Maestro\Extension\Report\ReportExtension;
use Maestro\Extension\Runner\RunnerExtension;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\Console\ConsoleExtension;
use Phpactor\MapResolver\Resolver;

class DotExtension implements Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        $container->register(DotReport::class, function (Container $container) {
            return new DotReport(
                $container->getParameter(RunnerExtension::PARAM_WORKING_DIRECTORY),
                $container->get(ConsoleExtension::SERVICE_OUTPUT)
            );
        }, [
            ReportExtension::TAG_REPORT => [
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
