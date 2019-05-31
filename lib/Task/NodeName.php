<?php

namespace Maestro\Task;

use RuntimeException;

final class NodeName
{
    /**
     * @var array
     */
    private $parts;

    private function __construct(array $parts)
    {
        if (empty($parts)) {
            throw new RuntimeException(
                'Name must have at least one part'
            );
        }

        $this->parts = $parts;
    }

    public static function fromUnknown($name): self
    {
        if (is_string($name)) {
            return new self([$name]);
        }

        if ($name instanceof self) {
            return $name;
        }

        throw new RuntimeException(sprintf(
            'Do not know how to create a node name from "%s"',
            is_object($name) ? get_class($name) : gettype($name)
        ));
    }

    public static function fromParts(array $parts): self
    {
        return new self($parts);
    }

    public function toString(): string
    {
        return implode('/', $this->parts);
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function namespace(): ?self
    {
        $parts = $this->parts;
        array_pop($parts);

        if (empty($parts)) {
            return null;
        }

        return new self($parts);
    }

    public function shortName(): string
    {
        $parts = $this->parts;
        $head = array_pop($parts);

        return $head;
    }

    public function append(string $part): self
    {
        $parts = $this->parts;
        $parts[] = $part;

        return new self($parts);
    }
}
