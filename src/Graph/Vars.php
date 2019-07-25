<?php

namespace Maestro\Graph;

use ArrayIterator;
use Iterator;
use IteratorAggregate;
use RuntimeException;

class Vars implements IteratorAggregate
{
    private $vars = [];

    private function __construct(array $vars)
    {
        foreach ($vars as $name => $var) {
            $this->add($name, $var);
        }
    }

    public function get(string $name)
    {
        if (!isset($this->vars[$name])) {
            throw new RuntimeException(sprintf(
                'Var "%s" is not known, known vars: "%s"',
                $name,
                implode('", "', array_keys($this->vars))
            ));
        }

        return $this->vars[$name];
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->vars);
    }

    private function add(string $name, $value): void
    {
        $this->vars[$name] = $value;
    }

    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->vars);
    }

    public static function fromArray(array $vars)
    {
        return new self($vars);
    }

    public function merge(Vars $vars)
    {
        return new self(array_merge($this->vars, $vars->vars));
    }

    public static function create(array $array): self
    {
        return new self($array);
    }

    public function toArray(): array
    {
        return $this->vars;
    }
}
