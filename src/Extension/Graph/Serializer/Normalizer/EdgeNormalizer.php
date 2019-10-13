<?php

namespace Maestro\Extension\Graph\Serializer\Normalizer;

use Maestro\Library\Graph\Edge;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class EdgeNormalizer implements NormalizerInterface
{
    /**
     * {@inheritDoc}
     */
    public function normalize($edge, $format = null, array $context = [])
    {
        assert($edge instanceof Edge);
        return [
            'to' => $edge->to(),
            'from' => $edge->from(),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Edge;
    }
}
