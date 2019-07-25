<?php

namespace Maestro\Graph;

use Maestro\Workspace\Workspace;

final class EnvironmentBuilder
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

    public function __construct(Vars $vars = null, Workspace $workspace = null, EnvVars $env = null)
    {
        $this->vars = $vars ?: Vars::create([]);
        $this->workspace = $workspace;
        $this->env = $env ?: EnvVars::create([]);
    }

    public function withVars(array $vars): self
    {
        $this->vars = Vars::create($vars);
        return $this;
    }

    public function withWorkspace(Workspace $workspace): self
    {
        $this->workspace = $workspace;
        return $this;
    }

    public function mergeEnv(array $env): self
    {
        $this->env = $this->env->merge(EnvVars::create($env));
        return $this;
    }

    public function build(): Environment
    {
        return Environment::create([
            'vars' => $this->vars,
            'workspace' => $this->workspace,
            'env' => $this->env,
        ]);
    }
}
