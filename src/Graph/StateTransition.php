<?php

namespace Maestro\Graph;

class StateTransition
{
    /**
     * @var State
     */
    private $from;
    /**
     * @var State
     */
    private $to;

    public function __construct(State $from, State $to)
    {
        $this->from = $from;
        $this->to = $to;
    }

    public function from(): State
    {
        return $this->from;
    }

    public function to(): State
    {
        return $this->to;
    }
}
