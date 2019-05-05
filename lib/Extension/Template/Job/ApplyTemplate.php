<?php

namespace Maestro\Extension\Template\Job;

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
    private $from;

    /**
     * @var string
     */
    private $to;

    public function __construct(PackageDefinition $packageDefinition, string $from, string $to)
    {
        $this->package = $packageDefinition;
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

    public function description(): string
    {
        return sprintf('Applying template from "%s" to "%s"', $this->from, $this->to);
    }
}
