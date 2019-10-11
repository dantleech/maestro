<?php

namespace Maestro\Library\Composer;

use Maestro\Library\Artifact\Artifact;

class PackagistPackageInfo implements Artifact
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string|null
     */
    private $version;

    public function __construct(string $name, ?string $version = null)
    {
        $this->name = $name;
        $this->version = $version;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function latestVersion(): ?string
    {
        return $this->version;
    }
}
