<?php

namespace Maestro\Library\Support;

use Maestro\Library\Task\Artifact;
use Webmozart\PathUtil\Path;

class ManifestPath implements Artifact
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
