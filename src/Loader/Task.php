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
    private $args;

    /**
     * @var array
     */
    private $depends;

    public function __construct(string $type, array $args = [], array $depends = [])
    {
        $this->type = $type;
        $this->args = $args;
        $this->depends = $depends;
    }

    public function args(): array
    {
        return $this->args;
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
