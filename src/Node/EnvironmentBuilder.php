<?php

namespace Maestro\Node;

final class EnvironmentBuilder
{
    /**
     * @var array
     */
    private $parameters;

    public function __construct(array $parameters = [])
    {
        $this->parameters = $parameters;
    }

    public function withParameters(array $parameters): self
    {
        $this->parameters = $parameters;
        return $this;
    }

    public function build(): Environment
    {
        return Environment::create([
            'parameters' => $this->parameters
        ]);
    }
}
