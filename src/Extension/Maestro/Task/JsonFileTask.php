<?php

namespace Maestro\Extension\Maestro\Task;

use Maestro\Graph\Task;

class JsonFileTask implements Task
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

    public function description(): string
    {
        return sprintf('Updating/creating json file "%s"', $this->targetPath);
    }
}
