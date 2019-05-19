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
     * @var string|null
     */
    private $depends;

    public function __construct(string $type, array $parameters = [], string $depends = null)
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

    public function depends(): ?string
    {
        return $this->depends;
    }
}
