<?php

namespace Maestro\Loader;

class Schedule
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var array
     */
    private $args;

    public function __construct(string $type, array $args = [])
    {
        $this->type = $type;
        $this->args = $args;
    }

    public function args(): array
    {
        return $this->args;
    }

    public function type(): string
    {
        return $this->type;
    }
}
