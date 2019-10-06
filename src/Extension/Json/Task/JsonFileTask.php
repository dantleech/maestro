<?php

namespace Maestro\Extension\Json\Task;

use Maestro\Library\Task\Task;

class JsonFileTask implements Task
{
    /**
     * @var string
     */
    private $targetPath;

    /**
     * @var array
     */
    private $data;

    public function __construct(string $targetPath, array $data = [])
    {
        $this->targetPath = $targetPath;
        $this->data = $data;
    }

    public function data(): array
    {
        return $this->data;
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
