<?php

namespace Maestro\Extension\Composer\Survery;

use Maestro\Library\Survey\SurveyResult;

class ComposerConfigResult implements SurveyResult
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
