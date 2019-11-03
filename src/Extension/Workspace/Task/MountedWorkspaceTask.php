<?php

namespace Maestro\Extension\Workspace\Task;

use Maestro\Library\Task\Task;

class MountedWorkspaceTask implements Task
{
    /**
     * @var string
     */
    private $host;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $name;

    public function __construct(string $name, string $host, string $path)
    {
        $this->host = $host;
        $this->path = $path;
        $this->name = $name;
    }

    public function description(): string
    {
        return sprintf(
            'creating mounted workspace "%s" at "%s:%s"',
            $this->name,
            $this->host,
            $this->path
        );
    }

    public function host(): string
    {
        return $this->host;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function path(): string
    {
        return $this->path;
    }
}
