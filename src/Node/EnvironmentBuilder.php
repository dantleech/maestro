<?php

namespace Maestro\Node;

use Maestro\Script\EnvVars;
use Maestro\Workspace\Workspace;

final class EnvironmentBuilder
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

    public function withParameters(array $parameters): self
    {
        $this->parameters = $parameters;
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
            'parameters' => $this->parameters,
            'workspace' => $this->workspace,
            'envVars' => $this->envVars,
        ]);
    }
}
