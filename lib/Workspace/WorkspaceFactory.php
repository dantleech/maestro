<?php


namespace Maestro\Workspace;

use Webmozart\PathUtil\Path;
use function Safe\glob;

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
        $workspacePath = Path::join([$this->rootPath, $this->namespace, $this->pathStrategy->packageNameToSubPath($name)]);

        return new Workspace($workspacePath, $name);
    }

    public function listWorkspaces(): Workspaces
    {
        return new Workspaces(array_map(function (string $path) {
            return $this->createNamedWorkspace($this->pathStrategy->subPathToPackageName(substr(
                $path,
                strlen(
                    Path::join($this->rootPath, $this->namespace)
                ) + 1
            )));
        }, glob(
            Path::join(
                $this->rootPath,
                $this->namespace,
                $this->pathStrategy->listingGlobPattern()
            )
        )));
    }
}
