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

    /**
     * @var PathStrategy
     */
    private $pathStrategy;

    public function __construct(PathStrategy $pathStrategy, string $namespace, string $rootPath)
    {
        $this->rootPath = $rootPath;
        $this->namespace = $namespace;
        $this->pathStrategy = $pathStrategy;
    }

    public function createNamedWorkspace(string $name): Workspace
    {
        $workspacePath = Path::join([$this->rootPath, $this->namespace, $this->pathStrategy->packageNameToPath($name)]);

        return new Workspace($workspacePath);
    }
}
