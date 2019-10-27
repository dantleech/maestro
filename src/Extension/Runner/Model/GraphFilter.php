<?php

namespace Maestro\Extension\Runner\Model;

use Maestro\Library\Graph\Graph;
use Maestro\Library\Graph\Node;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\ParsedExpression;
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
                $this->serializer->normalize($node)
            )) {
                $nodeIds[] = $node->id();
            }
        }

        return $graph->pruneFor($nodeIds);
    }
}
