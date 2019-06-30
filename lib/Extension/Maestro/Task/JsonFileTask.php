<?php

namespace Maestro\Extension\Maestro\Task;

class JsonFileTask
{
    /**
     * @var string
     */
    private $targetPath;

    /**
     * @var array
     */
    private $merge;

    public function __construct(string $targetPath, array $merge = [])
    {
        $this->targetPath = $targetPath;
        $this->merge = $merge;
    }

    public function merge(): array
    {
        return $this->merge;
    }

    public function targetPath(): string
    {
        return $this->targetPath;
    }
}
