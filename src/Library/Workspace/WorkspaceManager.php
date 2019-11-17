<?php


namespace Maestro\Library\Workspace;

use Webmozart\PathUtil\Path;

class WorkspaceManager
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

    /**
     * @var WorkspaceRegistry
     */
    private $registry;

    public function __construct(
        PathStrategy $pathStrategy,
        WorkspaceRegistry $registry,
        string $namespace,
        string $rootPath
    ) {
        $this->rootPath = $rootPath;
        $this->namespace = $namespace;
        $this->pathStrategy = $pathStrategy;
        $this->registry = $registry;
    }

    public function createNamedWorkspace(string $name): Workspace
    {
        $workspacePath = Path::join([$this->rootPath, $this->namespace, $this->pathStrategy->packageNameToSubPath($name)]);

        $workspace = new Workspace($workspacePath, $name);
        $this->registry->register($workspace);

        return $workspace;
    }
}
