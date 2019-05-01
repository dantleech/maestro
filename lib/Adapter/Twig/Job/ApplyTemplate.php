<?php

namespace Maestro\Adapter\Twig\Job;

use Maestro\Model\Job\Job;
use Maestro\Model\Package\ManifestItem;
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
    private $from;

    /**
     * @var string
     */
    private $to;

    public function __construct(PackageDefinition $package, string $from, string $to)
    {
        $this->package = $package;
        $this->from = $from;
        $this->to = $to;
    }

    public function package(): PackageDefinition
    {
        return $this->package;
    }

    public function to(): string
    {
        return $this->to;
    }

    public function from(): string
    {
        return $this->from;
    }
}
