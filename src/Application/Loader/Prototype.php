<?php

namespace Maestro\Application\Loader;

use Maestro\Library\Instantiator\Instantiator;

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

    /**
     * @var bool
     */
    private $purgeWorkspace;

    public function __construct(string $name, array $tasks = [], bool $purgeWorkspace = false)
    {
        $this->name = $name;

        foreach ($tasks as $name => $task) {
            $this->tasks[$name] = Instantiator::create(Task::class, $task);
        }
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

    public function purgeWorkspace(): bool
    {
        return $this->purgeWorkspace;
    }
}
