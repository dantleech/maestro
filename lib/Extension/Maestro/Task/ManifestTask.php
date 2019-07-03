<?php

namespace Maestro\Extension\Maestro\Task;

use Maestro\Node\Task;

class ManifestTask implements Task
{
    /**
     * @var string|null
     */
    private $path;

    /**
     * @var array
     */
    private $artifacts;

    public function __construct(?string $path, array $artifacts = [])
    {
        $this->path = $path;
        $this->artifacts = $artifacts;
    }

    public function description(): string
    {
        return 'Manifest';
    }

    public function path(): ?string
    {
        return $this->path;
    }

    public function artifacts(): array
    {
        return $this->artifacts;
    }
}
