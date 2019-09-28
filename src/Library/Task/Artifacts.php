<?php

namespace Maestro\Library\Task;

use Countable;
use Maestro\Library\Task\Exception\ArtifactNotFound;
use Maestro\Library\Task\Artifacts;

class Artifacts implements Countable
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

    public function set(object $artifact): void
    {
        $this->artifacts[get_class($artifact)] = $artifact;
    }

    public function spawnMutated(array $artifacts): Artifacts
    {
        return new self(array_merge($this->artifacts, $artifacts));
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
}
