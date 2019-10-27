<?php

namespace Maestro\Extension\Runner\Model;

use Maestro\Library\Graph\Graph;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

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

    public function filter(Graph $graph, string $filter): Graph
    {
        if (empty($filter)) {
            return $graph;
        }

        $expression  = new ExpressionLanguage();
        $expression->register('path', function ($val, $a) {
            return '';
        }, function ($node, $val) {
            return false !== strpos($node['id'], $val);
        });

        $nodeIds = [];
        foreach ($graph->nodes() as $node) {
            if ($expression->evaluate(
                $filter,
                (array)$this->serializer->normalize($node)
            )) {
                $nodeIds[] = $node->id();
            }
        }

        return $graph->pruneFor($nodeIds);
    }
}
