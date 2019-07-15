<?php

namespace Maestro\Loader\Loader;

use Maestro\Loader\Instantiator;
use Maestro\Loader\Loader;
use Maestro\Loader\Task;

class TaskLoader implements Loader
{
    /**
     * @var Task[]
     */
    private $tasks;

    public function __construct(array $tasks)
    {
        $this->tasks = $tasks;
    }

    public function tasks(): array
    {
        return array_map(function (array $task) {
            return Instantiator::create()->instantiate(Task::class, $task);
        }, $this->tasks);
    }
}
