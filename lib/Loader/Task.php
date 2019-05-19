<?php

namespace Maestro\Loader;

class Task
{
    /**
     * @var string
     */
    private $type;
    /**
     * @var array
     */
    private $parameters;

    public function __construct(string $type, array $parameters)
    {
        $this->type = $type;
        $this->parameters = $parameters;
    }

    public function parameters(): array
    {
        return $this->parameters;
    }

    public function type(): string
    {
        return $this->type;
    }
}
