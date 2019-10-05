<?php

namespace Maestro\Extension\File\Task;

use Maestro\Library\Task\Task;

class PurgeDirectoryTask implements Task
{
    /**
     * @var string
     */
    private $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function description(): string
    {
        return sprintf('purging directory "%s"', $this->path);
    }

    public function path(): string
    {
        return $this->path;
    }
}
