<?php

namespace Maestro\Script;

class EnvVars
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
}
