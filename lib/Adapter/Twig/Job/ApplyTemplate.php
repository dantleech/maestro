<?php

namespace Maestro\Adapter\Twig\Job;

use Maestro\Model\Job\Job;
use Maestro\Model\Package\PackageDefinition;

class ApplyTemplate implements Job
{
    /**
     * @var PackageDefinition
     */
    private $package;

    /**
     * @var string
     */
    private $sourcePath;

    /**
     * @var string
     */
    private $destinationPath;

    public function __construct(PackageDefinition $package, string $sourcePath, string $destinationPath)
    {
        $this->package = $package;
        $this->sourcePath = $sourcePath;
        $this->destinationPath = $destinationPath;
    }

    public function handler(): string
    {
        return ApplyTemplateHandler::class;
    }

    public function package(): PackageDefinition
    {
        return $this->package;
    }

    public function sourcePath(): string
    {
        return $this->sourcePath;
    }

    public function destinationPath(): string
    {
        return $this->destinationPath;
    }
}
