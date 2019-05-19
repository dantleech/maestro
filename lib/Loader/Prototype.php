<?php

namespace Maestro\Loader;

class Prototype
{
    /**
     * @var array
     */
    private $tasks;

    /**
     * @var string
     */
    private $name;

    public function __construct(string $name, array $tasks)
    {
        $this->name = $name;

        foreach ($tasks as $task) {
            $this->tasks[] = Instantiator::create()->instantiate(Task::class, $task);
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
