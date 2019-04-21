<?php

namespace Maestro\Model\Package;

class Workspace
{
    /**
     * @var string
     */
    private $workspacePath;

    public function __construct(string $workspacePath)
    {
        $this->workspacePath = $workspacePath;
    }

    public function package(PackageDefinition $package): PackageWorkspace
    {
        return new PackageWorkspace($this->workspacePath);
    }
}
