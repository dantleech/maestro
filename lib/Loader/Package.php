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
     * @var array
     */
    private $parameters = [];

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
        array $parameters = [],
        bool $purgeWorkspace = false,
        array $artifacts = []
    ) {
        $this->name = $name;

        foreach ($tasks as $name => $task) {
            $this->tasks[$name] = Instantiator::create()->instantiate(Task::class, $task);
        }
        $this->parameters = $parameters;
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

    public function parameters(): array
    {
        return $this->parameters;
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
