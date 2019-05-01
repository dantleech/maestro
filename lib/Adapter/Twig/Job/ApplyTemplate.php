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
     * @var ManifestItem
     */
    private $item;

    public function __construct(PackageDefinition $package, ManifestItem $item)
    {
        $this->package = $package;
        $this->item = $item;
    }

    public function package(): PackageDefinition
    {
        return $this->package;
    }

    public function item(): ManifestItem
    {
        return $this->item;
    }
}
