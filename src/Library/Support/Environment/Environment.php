<?php

namespace Maestro\Library\Support\Environment;

use Maestro\Library\Artifact\Artifact;

class Environment implements Artifact
{
    /**
     * @var array
     */
    public $env;

    public function __construct(array $env = [])
    {
        $this->env = $env;
    }

    public function toArray(): array
    {
        return $this->env;
    }

    public function spawnMerged(array $env): Environment
    {
        return new self(array_merge($this->env, $env));
    }
}
