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
     * @var string
     */
    private $prototype;

    /**
     * @var array
     */
    private $parameters = [];

    public function __construct(string $name, string $prototype = null, array $tasks = [], array $parameters = [])
    {
        $this->name = $name;

        foreach ($tasks as $name => $task) {
            $this->tasks[$name] = Instantiator::create()->instantiate(Task::class, $task);
        }
        $this->prototype = $prototype;
        $this->parameters = $parameters;
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
}
