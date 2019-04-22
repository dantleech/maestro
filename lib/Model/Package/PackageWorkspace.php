<?php

namespace Maestro\Model\Package;

use Symfony\Component\Filesystem\Filesystem;

class PackageWorkspace
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(string $path)
    {
        $this->path = $path;
        $this->filesystem = new Filesystem();
    }

    public function path(): string
    {
        return $this->path;
    }

    public function remove(): void
    {
        if (!file_exists($this->path)) {
            return;
        }

        $this->filesystem->remove($this->path);
    }
}
