<?php

namespace Maestro\Task;

final class NodeVisitorDecision
{
    private const DO_NOT_WALK_CHILDREN = 'DO_NOT_WALK_CHILDREN';
    private const CONTINUE = 'CONTINUE';

    /**
     * @var string
     */
    private $action;

    private function __construct(string $action)
    {
        $this->action = $action;
    }

    public function CONTINUE(): self
    {
        return new self(self::CONTINUE);
    }

    public function DO_NOT_WALK_CHILDREN(): self
    {
        return new self(self::DO_NOT_WALK_CHILDREN);
    }

    public function is(NodeVisitorDecision $nodeVisitorDecision): bool
    {
        return $nodeVisitorDecision->action === $this->action;
    }
}
