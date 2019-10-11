<?php

namespace Maestro\Library\Support\Package;

use Maestro\Library\Task\Artifact;

class Package implements Artifact
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string|null
     */
    private $version;

    public function __construct(string $name, ?string $version)
    {
        $this->name = $name;
        $this->version = $version;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function version(): ?string
    {
        return $this->version;
    }
}
