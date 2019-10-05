<?php

namespace Maestro\Extension\Report;

use Maestro\Extension\Report\Extension\ConsoleReportDefinition;
use Maestro\Extension\Report\Model\ConsoleReportRegistry;
use Maestro\Library\Instantiator\Instantiator;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\MapResolver\Resolver;

class ReportExtension implements Extension
{
    const TAG_REPORT_CONSOLE = 'report.console';

    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        $container->register(ConsoleReportRegistry::class, function (Container $container) {
            $reports = [];
            foreach ($container->getServiceIdsForTag(self::TAG_REPORT_CONSOLE) as $serviceId => $definition) {
                $definition = Instantiator::instantiate(ConsoleReportDefinition::class, array_merge([
                    'serviceId' => $serviceId,
                ], $definition));
                assert($definition instanceof ConsoleReportDefinition);
                $reports[$definition->name()] = $container->get($definition->serviceId());
            }

            return new ConsoleReportRegistry($reports);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema)
    {
    }
}
