<?php

namespace Maestro\Workspace;

use Webmozart\PathUtil\Path;

class Workspace
{
    /**
     * @var string
     */
    private $rootPath;

    public function __construct(string $rootPath)
    {
        $this->rootPath = $rootPath;
    }

    public function absolutePath(?string $relative = null): string
    {
        if ($relative === null) {
            return $this->rootPath;
        }
        return Path::join([$this->rootPath, $relative]);
    }
}
