<?php

namespace Maestro\Node;

final class NodeId
{
    /**
     * @var string
     */
    private $id;

    private function __construct(string $id)
    {
        $this->id = $id;
    }

    public static function fromString(string $id)
    {
        return new self($id);
    }

    public function toString(): string
    {
        return $this->id;
    }
}
