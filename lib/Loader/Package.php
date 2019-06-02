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
     * @var string|null
     */
    private $prototype;

    /**
     * @var array
     */
    private $parameters = [];

    /**
     * @var bool
     */
    private $purgeWorkspace;

    public function __construct(
        string $name,
        string $prototype = null,
        array $tasks = [],
        array $parameters = [],
        bool $purgeWorkspace = false
    ) {
        $this->name = $name;

        foreach ($tasks as $name => $task) {
            $this->tasks[$name] = Instantiator::create()->instantiate(Task::class, $task);
        }
        $this->prototype = $prototype;
        $this->parameters = $parameters;
        $this->purgeWorkspace = $purgeWorkspace;
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

    public function prototype(): ?string
    {
        return $this->prototype;
    }

    public function parameters(): array
    {
        return $this->parameters;
    }

    public function purgeWorkspace(): bool
    {
        return $this->purgeWorkspace;
    }
}
