<?php

namespace Maestro\Library\Support;

use Webmozart\PathUtil\Path;

class ManifestPath
{
    /**
     * @var string
     */
    private $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function directoryPath(): string
    {
        return Path::getDirectory($this->path);
    }
}
