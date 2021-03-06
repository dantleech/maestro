<?php

namespace Maestro\Extension\Task\Serializer\Normalizer;

use Maestro\Library\Artifact\Artifact;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ArtifactNormalizer implements NormalizerInterface
{
    /**
     * @var NormalizerInterface
     */
    private $normalizer;

    public function __construct(NormalizerInterface $normalizer)
    {
        $this->normalizer = $normalizer;
    }
    /**
     * {@inheritDoc}
     */
    public function normalize($artifact, $format = null, array $context = [
    ])
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
