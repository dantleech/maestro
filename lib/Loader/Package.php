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

    public function __construct(string $name, string $prototype = null, array $tasks = [])
    {
        $this->name = $name;

        foreach ($tasks as $name => $task) {
            $this->tasks[] = Instantiator::create()->instantiate(Task::class, $task);
        }
        $this->prototype = $prototype;
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
}
