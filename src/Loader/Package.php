<?php

namespace Maestro\Loader;

use RuntimeException;

class Package
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var array
     */
    private $tasks = [];

    /**
     * @var bool
     */
    private $purgeWorkspace;

    /**
     * @var array
     */
    private $artifacts;

    /**
     * @var array
     */
    private $loaders;

    public function __construct(
        string $name,
        array $tasks = [],
        array $loaders = [],
        bool $purgeWorkspace = false,
        array $artifacts = []
    ) {
        $this->name = $name;

        foreach ($tasks as $name => $task) {
            $this->tasks[$name] = Instantiator::create()->instantiate(Task::class, $task);
        }

        foreach ($loaders as $name => $loader) {
            if (!isset($loader['type'])) {
                throw new RuntimeException(
                    '"type" key must be set for each loader'
                );
            }

            $this->loaders[$name] = Instantiator::create()->instantiate($loader['type'], $loader);
        }

        $this->purgeWorkspace = $purgeWorkspace;
        $this->artifacts = $artifacts;
    }

    /**
     * @return Task[]
     */
    public function tasks(): array
    {
        return $this->tasks;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function purgeWorkspace(): bool
    {
        return $this->purgeWorkspace;
    }

    public function artifacts(): array
    {
        return $this->artifacts;
    }

    /**
     * @return NodeLoader[]
     */
    public function loaders(): array
    {
        return $this->loaders;
    }
}
