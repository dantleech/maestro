<?php

namespace Maestro\Workspace;

use ArrayIterator;
use Iterator;
use IteratorAggregate;
use RuntimeException;

class Workspaces implements IteratorAggregate
{
    private $workspaces = [];

    public function __construct(array $workspaces)
    {
        foreach ($workspaces as $workspace) {
            $this->add($workspace);
        }
    }

    private function add(Workspace $workspace): void
    {
        $this->workspaces[] = $workspace;
    }

    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->workspaces);
    }

    public function first(): Workspace
    {
        if (empty($this->workspaces)) {
            throw new RuntimeException(
                'Cannot get first workspace when there are no workspaces'
            );
        }
        return reset($this->workspaces);
    }
}
