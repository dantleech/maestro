<?php

namespace Maestro\Extension\Survey\Graph;

use Maestro\Extension\Survey\Task\SurveyTask;
use Maestro\Graph\Edge;
use Maestro\Graph\Graph;
use Maestro\Graph\Node;

class SurveyModifier
{
    public function modify(Graph $graph)
    {
        $builder = $graph->builder();

        foreach ($graph->leafs() as $leaf) {
            $parentId = $leaf->id();
            $builder->addNode(Node::create($parentId. '/survey', [
                'label' => 'package survey',
                'task' => new SurveyTask(),
                'tags' => ['version_info'],
            ]));
            $builder->addEdge(Edge::create($parentId. '/survey', $parentId));
        }

        return $builder->build();
    }
}
