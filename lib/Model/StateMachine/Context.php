<?php

namespace Phpactor\Extension\Maestro\Model\StateMachine;

use Phpactor\Extension\Maestro\Model\StateMachine\Exception\UnknownContextKey;

class Context
{
    private $context = [];

    public function get(string $name)
    {
        if (!isset($this->context[$name])) {
            throw new UnknownContextKey(sprintf(
                'Unknown context key "%s", known keys "%s"',
                $name, implode('", "', array_keys($this->context))
            ));
        }

        return $this->context[$name];
    }

    public function set(string $name, $value): void
    {
        $this->context[$name] = $value;
    }

    public function remove(string $name): void
    {
        unset($this->context[$name]);
    }

    public function has(string $string): bool
    {
        return isset($this->context[$string]);
    }
}
