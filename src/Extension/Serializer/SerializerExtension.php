<?php

namespace Maestro\Extension\Serializer;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\MapResolver\Resolver;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;

class SerializerExtension implements Extension
{
    const TAG_NORMALIZER = 'serializer.normalizer';
    const TAG_ENCODER = 'serializer.encoder';


    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        $container->register(Serializer::class, function (Container $container) {
            return new Serializer(
                $this->collectNormalizers($container),
                $this->collectEncoders($container)
            );
        });

        $this->registerNormalizers($container);
        $this->registerEncoders($container);
    }

    private function registerNormalizers(ContainerBuilder $container): void
    {
        $container->register(PropertyNormalizer::class, function () {
            return new PropertyNormalizer();
        }, [ self::TAG_NORMALIZER => []]);
        
        $container->register(ObjectNormalizer::class, function () {
            return new ObjectNormalizer();
        }, [ self::TAG_NORMALIZER => []]);
    }

    private function registerEncoders(ContainerBuilder $container): void
    {
        $container->register(JsonEncoder::class, function () {
            return new JsonEncoder();
        }, [ self::TAG_ENCODER => []]);
    }

    private function collectNormalizers(Container $container)
    {
        return array_map(function (string $serviceId) use ($container) {
            return $container->get($serviceId);
        }, array_keys($container->getServiceIdsForTag(self::TAG_NORMALIZER)));
    }

    private function collectEncoders(Container $container)
    {
        return array_map(function (string $serviceId) use ($container) {
            return $container->get($serviceId);
        }, array_keys($container->getServiceIdsForTag(self::TAG_ENCODER)));
    }

}
