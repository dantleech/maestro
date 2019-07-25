<?php

namespace Maestro\Node;

use Maestro\Loader\Instantiator;
use Maestro\Workspace\Workspace;
use RuntimeException;

final class Environment
{
    /**
     * @var Vars
     */
    private $vars;

    /**
     * @var Workspace|null
     */
    private $workspace;

    /**
     * @var EnvVars
     */
    private $env;

    public function __construct(
        Vars $vars = null,
        Workspace $workspace = null,
        EnvVars $env = null
    ) {
        $this->vars = $vars ?: Vars::create([]);
        $this->workspace = $workspace;
        $this->env = $env ?: EnvVars::create([]);
    }

    public static function create(array $data = []): self
    {
        return Instantiator::create()->instantiate(self::class, $data);
    }

    public static function empty(): self
    {
        return new self();
    }

    public function merge(Environment $environment): self
    {
        return new self(
            $this->vars->merge($environment->vars),
            $environment->hasWorkspace() ? $environment->workspace() : $this->workspace,
            $this->env->merge($environment->env())
        );
    }

    public function vars(): Vars
    {
        return $this->vars;
    }

    public function builder(): EnvironmentBuilder
    {
        return new EnvironmentBuilder($this->vars);
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

    public function env(): EnvVars
    {
        return $this->env;
    }

    public function debugInfo(): array
    {
        return [
            'env' => $this->env->toArray(),
            'vars' => $this->vars->toArray(),
            'workspace' => $this->workspace ? $this->workspace->absolutePath() : null,
        ];
    }
}
