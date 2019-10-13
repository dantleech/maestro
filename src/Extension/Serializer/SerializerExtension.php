<?php

namespace Maestro\Extension\Serializer;

use Maestro\Extension\Report\ReportExtension;
use Maestro\Extension\Serializer\Extension\NormalizerDefinition;
use Maestro\Extension\Serializer\Report\JsonReport;
use Maestro\Library\Instantiator\Instantiator;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\Console\ConsoleExtension;
use Phpactor\MapResolver\Resolver;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerAwareInterface;

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
            $serializer = new Serializer(
                $this->collectNormalizers($container),
                $this->collectEncoders($container)
            );
            return $serializer;
        });

        $this->registerNormalizers($container);
        $this->registerEncoders($container);
        $this->registerReports($container);
    }

    private function registerNormalizers(ContainerBuilder $container): void
    {
        $container->register(ObjectNormalizer::class, function () {
            return new ObjectNormalizer();
        }, [ self::TAG_NORMALIZER => [
            'priority' => -10,
        ]]);

    }

    private function registerEncoders(ContainerBuilder $container): void
    {
        $container->register(JsonEncoder::class, function () {
            return new JsonEncoder();
        }, [ self::TAG_ENCODER => []]);
    }

    private function collectNormalizers(Container $container)
    {
        $definitions = array_map(function (string $serviceId, array $attrs) use ($container) {
            return Instantiator::instantiate(NormalizerDefinition::class, array_merge([
                'serviceId' => $serviceId,
            ], $attrs));
        }, array_keys(
            $container->getServiceIdsForTag(self::TAG_NORMALIZER)
        ), $container->getServiceIdsForTag(self::TAG_NORMALIZER));

        usort($definitions, function (NormalizerDefinition $a, NormalizerDefinition $b) {
            return $b->priority() <=> $a->priority();
        });

        return array_map(function (NormalizerDefinition $definition) use ($container) {
            return $container->get($definition->serviceId());
        }, $definitions);
    }

    private function collectEncoders(Container $container)
    {
        return array_map(function (string $serviceId) use ($container) {
            return $container->get($serviceId);
        }, array_keys($container->getServiceIdsForTag(self::TAG_ENCODER)));
    }

    private function registerReports(ContainerBuilder $container)
    {
        $container->register(JsonReport::class, function (Container $container) {
            return new JsonReport(
                $container->get(Serializer::class),
                $container->get(ConsoleExtension::SERVICE_OUTPUT)
            );
        }, [
            ReportExtension::TAG_REPORT => [
                'name' => 'json',
            ]
        ]);
    }
}
