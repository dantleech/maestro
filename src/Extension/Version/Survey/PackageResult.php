<?php

namespace Maestro\Extension\Version\Survey;

class PackageResult
{
    /**
     * @var string|null
     */
    private $branchAlias;

    public function __construct(string $branchAlias = null)
    {
        $this->branchAlias = $branchAlias;
    }

    public function branchAlias(): ?string
    {
        return $this->branchAlias;
    }
}
