<?php

namespace Maestro\Node;

use Maestro\Node\Exception\ArtifactNotFound;

/**
 * Environment are the map made available by ancestoral tasks.
 *
 * They can be used for example, to make the package name available to
 * dependent tasks, or provide the workspace, passwords, whatever.
 */
final class Environment
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

    public static function empty(): self
    {
        return new self([]);
    }

    public function get(string $key)
    {
        if (!isset($this->map[$key])) {
            throw new ArtifactNotFound(sprintf(
                'Artifact "%s" not known, probably caused by a missing dependency. Known keys: "%s"',
                $key,
                implode('", "', array_keys($this->map))
            ));
        }

        return $this->map[$key];
    }

    public function merge(Environment $environment): self
    {
        return self::create(array_merge($this->map, $environment->map));
    }

    public function toArray(): array
    {
        return $this->map;
    }

    public function has(string $string): bool
    {
        return isset($this->map[$string]);
    }
}
