<?php

namespace Maestro\Node;

class Edge
{
    /**
     * @var string
     */
    private $from;

    /**
     * @var string
     */
    private $to;

    private function __construct(string $from, string $to)
    {
        $this->from = $from;
        $this->to = $to;
    }

    public static function create(string $from, string $to)
    {
        return new self($from, $to);
    }

    public function from(): string
    {
        return $this->from;
    }

    public function to(): string
    {
        return $this->to;
    }
}
