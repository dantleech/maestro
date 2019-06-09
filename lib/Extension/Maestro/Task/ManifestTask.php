<?php

namespace Maestro\Extension\Maestro\Task;

use Maestro\Task\Task;

class ManifestTask implements Task
{
    /**
     * @var string|null
     */
    private $path;

    public function __construct(?string $path)
    {
        $this->path = $path;
    }

    public function description(): string
    {
        return 'Manifest';
    }

    public function path(): ?string
    {
        return $this->path;
    }
}
