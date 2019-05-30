<?php

namespace Maestro\Task;

interface NodeVisitor
{
    public function visit(Graph $graph, Node $node): NodeVisitorDecision;
}
