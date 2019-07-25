<?php

namespace Maestro\Extension\Twig\Task;

use Maestro\Graph\Task;

class TemplateTask implements Task
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $targetPath;

    public function __construct(string $path, string $targetPath)
    {
        $this->path = $path;
        $this->targetPath = $targetPath;
    }

    public function description(): string
    {
        return sprintf('applying template "%s" to "%s"', $this->path, $this->targetPath);
    }

    public function path(): string
    {
        return $this->path;
    }

    public function targetPath(): string
    {
        return $this->targetPath;
    }
}
