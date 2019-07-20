<?php

namespace Maestro\Script;

use JsonSerializable;
use RuntimeException;

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

    public function merge(EnvVars $envVars): self
    {
        return new self(array_merge($this->env, $envVars->env));
    }

    public function get(string $name)
    {
        if (!isset($this->env[$name])) {
            throw new RuntimeException(sprintf('Env var "%s" does not exist', $name));
        }

        return $this->env[$name];
    }
}
