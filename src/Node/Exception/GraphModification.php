<?php

namespace Maestro\Node\Exception;

use Exception;
use Maestro\Node\Graph;

class GraphModification extends Exception
{
    /**
     * @var Graph
     */
    private $replacementGraph;

    public function __construct(Graph $replacementGraph)
    {
        parent::__construct('Graph modification');
        $this->replacementGraph = $replacementGraph;
    }

    public function replacementGraph(): Graph
    {
        return $this->replacementGraph;
    }
}
