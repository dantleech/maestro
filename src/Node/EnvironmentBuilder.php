<?php

namespace Maestro\Node;

use Maestro\Script\EnvVars;
use Maestro\Workspace\Workspace;

final class EnvironmentBuilder
{
    /**
     * @var array
     */
    private $vars;

    /**
     * @var Workspace|null
     */
    private $workspace;

    /**
     * @var EnvVars
     */
    private $envVars;

    public function __construct(array $vars = [], Workspace $workspace = null, EnvVars $envVars = null)
    {
        $this->vars = $vars;
        $this->workspace = $workspace;
        $this->envVars = $envVars ?: EnvVars::create([]);
    }

    public function withVars(array $vars): self
    {
        $this->vars = $vars;
        return $this;
    }

    public function withWorkspace(Workspace $workspace): self
    {
        $this->workspace = $workspace;
        return $this;
    }

    public function mergeEnvVars(array $envVars): self
    {
        $this->envVars = $this->envVars->merge(EnvVars::create($envVars));
        return $this;
    }

    public function build(): Environment
    {
        return Environment::create([
            'vars' => $this->vars,
            'workspace' => $this->workspace,
            'envVars' => $this->envVars,
        ]);
    }
}
