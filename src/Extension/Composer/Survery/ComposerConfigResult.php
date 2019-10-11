<?php

namespace Maestro\Extension\Composer\Survery;

use Maestro\Library\Artifact\Artifact;

class ComposerConfigResult implements Artifact
{
    /**
     * @var ?string
     */
    private $branchAlias;

    public function __construct(?string $branchAlias = null)
    {
        $this->branchAlias = $branchAlias;
    }

    public function branchAlias(): ?string
    {
        return $this->branchAlias;
    }
}
