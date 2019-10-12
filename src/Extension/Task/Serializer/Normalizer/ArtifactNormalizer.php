<?php

namespace Maestro\Extension\Task\Serializer\Normalizer;

use Maestro\Extension\Artifact\Extension\ArtifactHandlerDefinitionMap;
use Maestro\Library\Artifact\Artifact;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;

class ArtifactNormalizer implements NormalizerInterface
{
    /**
     * @var PropertyNormalizer
     */
    private $normalizer;

    public function __construct(PropertyNormalizer $normalizer)
    {
        $this->normalizer = $normalizer;
    }
    /**
     * {@inheritDoc}
     */
    public function normalize($artifact, $format = null, array $context = array (
    ))
    {
        assert($artifact instanceof Artifact);

        return [
            'class' => get_class($artifact),
            'data' => $this->normalizer->normalize($artifact),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Artifact;
    }
}

