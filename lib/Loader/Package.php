<?php

namespace Maestro\Loader;

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

    public function __construct(
        string $name,
        array $tasks = [],
        bool $purgeWorkspace = false,
        array $artifacts = []
    ) {
        $this->name = $name;

        foreach ($tasks as $name => $task) {
            $this->tasks[$name] = Instantiator::create()->instantiate(Task::class, $task);
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
}
