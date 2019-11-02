<?php

namespace Maestro\Library\Support\Variables;

use Maestro\Library\Support\Variables\Exception\VariableNotFound;
use Maestro\Library\Artifact\Artifact;

class Variables implements Artifact
{
    /**
     * @var array
     */
    public $variables;

    public function __construct(array $variables = [])
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

    public function merge(Variables $variables): self
    {
        return new self(array_merge($this->variables, $variables->variables));
    }

    public function toArray(): array
    {
        return $this->variables;
    }

    public static function fromArray(array $array): self
    {
        return new self($array);
    }
}
