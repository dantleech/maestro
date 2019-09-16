<?php

namespace Maestro\Library\Support\Variables;

use Maestro\Library\Support\Variables\Exception\VariableNotFound;

class Variables
{
    /**
     * @var array
     */
    private $variables;

    public function __construct(array $variables)
    {
        $this->variables = $variables;
    }

    public function get(string $name)
    {
        if (!isset($this->variables[$name])) {
            throw new VariableNotFound($name, array_keys($this->variables));
        }

        return $this->variables[$name];
    }

    public function toArray(): array
    {
        return $this->variables;
    }
}
