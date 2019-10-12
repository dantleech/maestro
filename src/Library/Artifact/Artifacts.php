<?php

namespace Maestro\Library\Artifact;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Maestro\Library\Artifact\Exception\ArtifactNotFound;

class Artifacts implements Countable, IteratorAggregate
{
    /**
     * @var array<string,object>
     */
    private $artifactsByClass = [];

    /**
     * @var array
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
        if (!isset($this->artifactsByClass[$artifactFqn])) {
            throw new ArtifactNotFound($artifactFqn, array_keys($this->artifactsByClass));
        }

        return $this->artifactsByClass[$artifactFqn];
    }

    public function set(Artifact $artifact): void
    {
        $this->artifactsByClass[get_class($artifact)] = $artifact;
        $this->artifacts[] = $artifact;
    }

    public function spawnMutated(self $artifacts): Artifacts
    {
        return new self(array_merge($this->artifactsByClass, $artifacts->artifactsByClass));
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
        return isset($this->artifactsByClass[$artifactFqn]);
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return new ArrayIterator($this->artifacts);
    }
}
