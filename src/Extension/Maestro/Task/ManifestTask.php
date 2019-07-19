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
    private $environment;

    public function __construct(?string $path, array $environment = [])
    {
        $this->path = $path;
        $this->environment = $environment;
    }

    public function description(): string
    {
        return 'initalizing manifest';
    }

    public function path(): ?string
    {
        return $this->path;
    }

    public function environment(): array
    {
        return $this->environment;
    }
}
