<?php

namespace Maestro\Model\Unit;

final class Environment
{
    /**
     * @var Parameters
     */
    private $parameters;

    /**
     * @var string
     */
    private $group;

    private function __construct(string $group, Parameters $parameters)
    {
        $this->parameters = $parameters;
        $this->group = $group;
    }

    public static function new(string $group): self
    {
        return new self($group, Parameters::new());
    }

    public function group(): string
    {
        return $this->group;
    }

    public function parameters(): Parameters
    {
        return $this->parameters;
    }
}
