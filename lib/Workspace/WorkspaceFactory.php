<?php

namespace Maestro\Workspace;

use Webmozart\PathUtil\Path;

class WorkspaceFactory
{
    /**
     * @var string
     */
    private $rootPath;

    /**
     * @var string
     */
    private $namespace;

    public function __construct(string $namespace, string $rootPath)
    {
        $this->rootPath = $rootPath;
        $this->namespace = $namespace;
    }

    public function createNamedWorkspace(string $name): Workspace
    {
        $workspacePath = Path::join([$this->rootPath, $this->namespace, $this->normalize($name)]);

        return new Workspace($workspacePath);
    }

    private function normalize(string $name): string
    {
        return Path::normalize($name);
    }
}
