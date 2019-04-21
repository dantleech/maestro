<?php

namespace Maestro\Model\Package;

class Workspace
{
    /**
     * @var string
     */
    private $workspacePath;

    private function __construct(string $workspacePath)
    {
        $this->workspacePath = $workspacePath;
    }

    public static function create(string $workspacePath): self
    {
        return new self($workspacePath);
    }

    public function package(PackageDefinition $package): PackageWorkspace
    {
        return new PackageWorkspace($this->workspacePath);
    }
}
