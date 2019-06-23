<?php

namespace Maestro\Task;

interface NodeFactory
{
    public function create(string $id, ?string $label = null, ?Task $task = null): Node;
}
