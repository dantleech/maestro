<?php

namespace Maestro\Extension\Graph\Serializer\Normalizer;

use Maestro\Library\Graph\Graph;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class GraphNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * {@inheritDoc}
     */
    public function normalize($graph, $format = null, array $context = [])
    {
        assert($graph instanceof Graph);
        return [
            'edges' => $this->normalizer->normalize($graph->edges()->toArray()),
            'nodes' => $this->normalizer->normalize($graph->nodes()->toArray()),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Graph;
    }
}
