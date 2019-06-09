<?php

namespace Maestro\Script;

use JsonSerializable;

class EnvVars implements JsonSerializable
{
    /**
     * @var array
     */
    private $env;

    private function __construct(array $env)
    {
        $this->env = $env;
    }

    public static function create(array $vars): self
    {
        return new self($vars);
    }

    public function toArray(): array
    {
        return $this->env;
    }

    /**
     * {@inheritDoc}
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
