<?php

namespace Maestro\Library\GraphTaskRunner;

use Maestro\Library\GraphTaskRunner\Exception\ArtifactNotFound;

class ArtifactContainer
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

    public function spawnMutated(array $artifacts): ArtifactContainer
    {
        return new self(array_merge($this->artifacts, $artifacts));
    }

    public function toArray(): array
    {
        return $this->artifacts;
    }
}
