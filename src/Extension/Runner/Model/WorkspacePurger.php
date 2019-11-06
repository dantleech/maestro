<?php

namespace Maestro\Extension\Runner\Model;

class WorkspacePurger
{
    /**
     * @var string
     */
    private $workspacePath;

    public function __construct(string $workspacePath)
    {
        $this->workspacePath = $workspacePath;
    }

    public function purgeAll(): void
    {
    }
}
