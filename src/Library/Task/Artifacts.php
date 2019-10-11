<?php

namespace Maestro\Library\Task;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Maestro\Library\Task\Exception\ArtifactNotFound;

class Artifacts implements Countable, IteratorAggregate
{
    /**
     * @var array<string,object>
     */
    private $artifacts = [];

    public function __construct(array $artifacts = [])
    {
        foreach ($artifacts as $artifact) {
            $this->set($artifact);
        }
    }

    /**
     * @return object
     */
    public function get(string $artifactFqn): object
    {
        if (!isset($this->artifacts[$artifactFqn])) {
            throw new ArtifactNotFound($artifactFqn, array_keys($this->artifacts));
        }

        return $this->artifacts[$artifactFqn];
    }

    public function set(Artifact $artifact): void
    {
        $this->artifacts[get_class($artifact)] = $artifact;
    }

    public function spawnMutated(self $artifacts): Artifacts
    {
        return new self(array_merge($this->artifacts, $artifacts->artifacts));
    }

    public function toArray(): array
    {
        return $this->artifacts;
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return count($this->artifacts);
    }

    public function has(string $artifactFqn): bool
    {
        return isset($this->artifacts[$artifactFqn]);
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return new ArrayIterator($this->artifacts);
    }
}
