<?php

namespace Maestro\Extension\Graph;

use Maestro\Extension\Graph\Serializer\Normalizer\EdgeNormalizer;
use Maestro\Extension\Graph\Serializer\Normalizer\GraphNormalizer;
use Maestro\Extension\Graph\Serializer\Normalizer\NodeNormalizer;
use Maestro\Extension\Serializer\SerializerExtension;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\MapResolver\Resolver;

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
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema)
    {
    }
}
