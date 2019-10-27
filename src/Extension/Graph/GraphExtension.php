<?php

namespace Maestro\Extension\Graph;

use Maestro\Extension\Graph\Report\NodeReport;
use Maestro\Extension\Graph\Serializer\Normalizer\EdgeNormalizer;
use Maestro\Extension\Graph\Serializer\Normalizer\GraphNormalizer;
use Maestro\Extension\Graph\Serializer\Normalizer\NodeNormalizer;
use Maestro\Extension\Report\ReportExtension;
use Maestro\Extension\Serializer\SerializerExtension;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\Console\ConsoleExtension;
use Phpactor\MapResolver\Resolver;
use Symfony\Component\Serializer\SerializerInterface;

class GraphExtension implements Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        $container->register(GraphNormalizer::class, function () {
            return new GraphNormalizer();
        }, [ SerializerExtension::TAG_NORMALIZER => []]);

        $container->register(EdgeNormalizer::class, function () {
            return new EdgeNormalizer();
        }, [ SerializerExtension::TAG_NORMALIZER => []]);

        $container->register(NodeNormalizer::class, function () {
            return new NodeNormalizer();
        }, [ SerializerExtension::TAG_NORMALIZER => []]);

        $container->register(NodeReport::class, function (Container $container) {
            return new NodeReport(
                $container->get(ConsoleExtension::SERVICE_OUTPUT),
                $container->get(SerializerInterface::class)
            );
        }, [ ReportExtension::TAG_REPORT => [
            'name' => 'node'
        ]]);
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema)
    {
    }
}
