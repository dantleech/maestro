<?php

namespace Maestro\Loader;

class Prototype
{
    /**
     * @var array
     */
    private $tasks = [];

    /**
     * @var string
     */
    private $name;

    public function __construct(string $name, array $tasks = [])
    {
        $this->name = $name;

        foreach ($tasks as $name => $task) {
            $this->tasks[$name] = Instantiator::create()->instantiate(Task::class, $task);
        }
    }

    public function tasks(): array
    {
        return $this->tasks;
    }

    public function name(): string
    {
        return $this->name;
    }
}
