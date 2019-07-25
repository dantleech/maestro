<?php

namespace Maestro\Graph;

class StateChangeEvent
{
    /**
     * @var Node
     */
    private $node;

    /**
     * @var State
     */
    private $from;

    /**
     * @var State
     */
    private $to;

    public function __construct(Node $node, State $from, State $to)
    {
        $this->node = $node;
        $this->from = $from;
        $this->to = $to;
    }

    public function from(): State
    {
        return $this->from;
    }

    public function node(): Node
    {
        return $this->node;
    }

    public function to(): State
    {
        return $this->to;
    }
}
