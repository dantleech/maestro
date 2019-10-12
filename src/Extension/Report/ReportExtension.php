<?php

namespace Maestro\Extension\Report;

use Maestro\Extension\Report\Extension\ConsoleReportDefinition;
use Maestro\Extension\Report\Model\MaestroGraphSerializer;
use Maestro\Extension\Task\Extension\TaskHandlerDefinitionMap;
use Maestro\Library\Report\GraphSerializer;
use Maestro\Library\Report\ReportRegistry;
use Maestro\Library\Instantiator\Instantiator;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\MapResolver\Resolver;

class ReportExtension implements Extension
{
    const TAG_REPORT = 'report.console';

    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        $container->register(ReportRegistry::class, function (Container $container) {
            $reports = [];
            foreach ($container->getServiceIdsForTag(self::TAG_REPORT) as $serviceId => $definition) {
                $definition = Instantiator::instantiate(ConsoleReportDefinition::class, array_merge([
                    'serviceId' => $serviceId,
                ], $definition));
                assert($definition instanceof ConsoleReportDefinition);
                $reports[$definition->name()] = $container->get($definition->serviceId());
            }

            return new ReportRegistry($reports);
        });

        $container->register(GraphSerializer::class, function (Container $container) {
            return new MaestroGraphSerializer($container->get(TaskHandlerDefinitionMap::class));
        });
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema)
    {
    }
}
