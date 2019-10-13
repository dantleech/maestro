<?php

namespace Maestro\Extension\Graph\Serializer\Normalizer;

use Maestro\Library\Graph\Node;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class NodeNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * {@inheritDoc}
     */
    public function normalize($node, $format = null, array $context = [])
    {
        assert($node instanceof Node);
        $exception = $node->exception();
        return [
            'id' => $node->id(),
            'label' => $node->label(),
            'state' => $node->state()->toString(),
            'tags' => $node->tags(),
            'exception' => $exception ? $exception->getMessage() : null,
            'task' => $this->normalizer->normalize($node->task()),
            'artifacts' => $this->normalizer->normalize($node->artifacts()),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Node;
    }
}
