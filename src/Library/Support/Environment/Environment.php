<?php

namespace Maestro\Library\Support\Environment;

class Environment
{
    /**
     * @var array
     */
    private $vars;

    public function __construct(array $vars)
    {
        $this->vars = $vars;
    }

    public function toArray(): array
    {
        return $this->vars;
    }
}
