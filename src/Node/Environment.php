<?php

namespace Maestro\Node;

use Maestro\Loader\Instantiator;
use Maestro\Node\Exception\ParameterNotFound;
use Maestro\Script\EnvVars;
use Maestro\Workspace\Workspace;
use RuntimeException;

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

    /**
     * @var Workspace|null
     */
    private $workspace;

    /**
     * @var EnvVars
     */
    private $envVars;

    public function __construct(array $parameters = [], Workspace $workspace = null, EnvVars $envVars = null)
    {
        $this->parameters = $parameters;
        $this->workspace = $workspace;
        $this->envVars = $envVars ?: EnvVars::create([]);
    }

    public static function create(array $parameters = []): self
    {
        return Instantiator::create()->instantiate(self::class, $parameters);
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
        return new self(
            array_merge($this->parameters, $environment->parameters),
            $environment->hasWorkspace() ? $environment->workspace() : $this->workspace,
            $this->envVars->merge($environment->envVars())
        );
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

    public function hasWorkspace(): bool
    {
        return null !== $this->workspace;
    }

    public function workspace(): Workspace
    {
        if (null === $this->workspace) {
            throw new RuntimeException(
                'Workspace has not been set'
            );
        }

        return $this->workspace;
    }

    public function envVars(): EnvVars
    {
        return $this->envVars;
    }
}
