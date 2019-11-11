<?php

namespace Maestro\Extension\Runner\Model;

use Maestro\Extension\Runner\Model\Exception\FilterError;
use Maestro\Library\Graph\Graph;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;
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
        $this->registerFunctions($expression);

        $nodeIds = [];
        foreach ($graph->nodes() as $node) {
            if ($this->evaluate(
                $expression,
                $filter,
                (array)$this->serializer->normalize($node)
            )) {
                $nodeIds[] = $node->id();
            }
        }

        return $graph->pruneFor($nodeIds);
    }

    private function registerFunctions(ExpressionLanguage $expression)
    {
        $expression->register('branch', function ($val, $a) {
            return '';
        }, function ($node, $val) {
            return false !== strpos($node['id'], $val);
        });
    }

    private function evaluate(ExpressionLanguage $expression, string $filter, array $variables)
    {
        try {
            return $expression->evaluate($filter, $variables);
        } catch (SyntaxError $syntaxError) {
            if (preg_match('{variable}i', $syntaxError->getMessage())) {
                throw new FilterError(sprintf(
                    'Variable may not have been found. Known variables: "%s"',
                    implode('", "', array_keys($variables))
                ));
            }
        }
    }
}
