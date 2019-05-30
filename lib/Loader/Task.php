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

    /**
     * @var array
     */
    private $depends;

    public function __construct(string $type, array $parameters = [], array $depends = [])
    {
        $this->type = $type;
        $this->parameters = $parameters;
        $this->depends = $depends;
    }

    public function parameters(): array
    {
        return $this->parameters;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function depends(): array
    {
        return $this->depends;
    }
}
