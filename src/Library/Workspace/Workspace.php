<?php

namespace Maestro\Library\Workspace;

use Maestro\Library\Artifact\Artifact;
use Symfony\Component\Filesystem\Filesystem;
use Webmozart\PathUtil\Path;

class Workspace implements Artifact
{
    /**
     * @var string
     */
    private $rootPath;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $name;

    public function __construct(string $rootPath, string $name)
    {
        $this->rootPath = $rootPath;
        $this->filesystem = new Filesystem();
        $this->name = $name;
    }

    public function absolutePath(?string $relative = null): string
    {
        if ($relative === null) {
            return $this->rootPath;
        }
        return Path::join([$this->rootPath, $relative]);
    }

    public function purge(): void
    {
        if (!file_exists($this->rootPath)) {
            return;
        }

        $this->filesystem->remove($this->rootPath);
    }

    public function name(): string
    {
        return $this->name;
    }
}
