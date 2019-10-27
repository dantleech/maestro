<?php

namespace Maestro\Extension\Runner\Model;

use Maestro\Library\Graph\Graph;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class GraphFilter
{
    /**
     * @var NormalizerInterface
     */
    private $serializer;

    public function __construct(NormalizerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function filter(Graph $graph, string $filter)
    {
        $expression  = new ExpressionLanguage();
        $nodeIds = [];
        foreach ($graph->nodes() as $node) {
            if ($expression->evaluate($filter,
                $this->serializer->normalize($node),
            )) {
                $nodeIds[] = $node->id();
            }
        }

        return $graph->pruneFor($nodeIds);
    }
}
