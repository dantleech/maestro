<?php

namespace Maestro\Node;

use Maestro\Node\Exception\ParameterNotFound;

/**
 * Environment are the map made available by ancestoral tasks.
 *
 * They can be used for example, to make the package name available to
 * dependent tasks, or provide the workspace, passwords, whatever.
 */
final class Environment
{
    /**
     * @var array
     */
    private $parameters;

    public function __construct(array $parameters)
    {
        $this->parameters = $parameters;
    }

    public static function create(array $parameters = []): self
    {
        return new self($parameters);
    }

    public static function empty(): self
    {
        return new self([]);
    }

    public function get(string $key)
    {
        if (!isset($this->parameters[$key])) {
            throw new ParameterNotFound(sprintf(
                'Parameter "%s" not known, probably caused by a missing dependency. Known keys: "%s"',
                $key,
                implode('", "', array_keys($this->parameters))
            ));
        }

        return $this->parameters[$key];
    }

    public function merge(Environment $environment): self
    {
        return new self(array_merge($this->parameters, $environment->parameters));
    }

    public function toArray(): array
    {
        return $this->parameters;
    }

    public function has(string $string): bool
    {
        return isset($this->parameters[$string]);
    }

    public function builder(): EnvironmentBuilder
    {
        return new EnvironmentBuilder($this->parameters);
    }
}
