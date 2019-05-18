<?php

namespace Maestro\Task;

use Maestro\Task\Exception\ArtifactNotFound;

/**
 * Artifacts are the map made available by ancestoral tasks.
 *
 * They can be used for example, to make the package name available to
 * dependent tasks, or provide the workspace, passwords, whatever.
 */
final class Artifacts
{
    /**
     * @var array
     */
    private $map;

    private function __construct(array $map)
    {
        $this->map = $map;
    }

    public static function create(array $map): self
    {
        return new self($map);
    }

    public function get(string $key)
    {
        if (!isset($this->map[$key])) {
            throw new ArtifactNotFound(sprintf(
                'Artifact "%s" not known, probably caused by a missing dependency. Known artifacts: "%s"',
                $key,
                implode('", "', array_keys($this->map))
            ));
        }

        return $this->map[$key];
    }

    public function merge(Artifacts $artifacts): self
    {
        return self::create(array_merge($this->map, $artifacts->map));
    }

    public function empty(): self
    {
        return new self([]);
    }
}
