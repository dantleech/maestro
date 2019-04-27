<?php

namespace Maestro\Adapter\Twig\Job;

use Maestro\Model\Job\Job;
use Maestro\Model\Package\PackageDefinition;

class ApplyTemplate implements Job
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var PackageDefinition
     */
    private $package;


    public function __construct(PackageDefinition $package, string $name)
    {
        $this->name = $name;
        $this->package = $package;
    }

    public function handler(): string
    {
        return ApplyTemplateHandler::class;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function package(): PackageDefinition
    {
        return $this->package;
    }
}
