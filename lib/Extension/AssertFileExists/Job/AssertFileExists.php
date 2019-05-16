<?php

declare(strict_types=1);

namespace Maestro\Extension\AssertFileExists\Job;

use Maestro\Model\Job\Job;
use Maestro\Model\Package\PackageDefinition;

class AssertFileExists implements Job
{
    /**
     * @var PackageDefinition
     */
    private $packageDefinition;

    /**
     * @var string
     */
    private $path;

    public function __construct(PackageDefinition $packageDefinition, string $path)
    {
        $this->packageDefinition = $packageDefinition;
        $this->path = $path;
    }

    public function package(): PackageDefinition
    {
        return $this->packageDefinition;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function description(): string
    {
        return sprintf('Checking that file "%s" exists', $this->path);
    }
}
