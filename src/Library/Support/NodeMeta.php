<?php

namespace Maestro\Library\Support;

use Maestro\Library\Artifact\Artifact;

class NodeMeta implements Artifact
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $path;

    public function __construct(string $name, string $path)
    {
        $this->name = $name;
        $this->path = $path;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function path(): string
    {
        return $this->path;
    }
}
