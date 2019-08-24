<?php

namespace Maestro\Extension\Composer\Survery;

use Amp\Promise;
use Maestro\Extension\Composer\Model\Packagist;
use Maestro\Extension\Survey\Model\Surveyor;
use Maestro\Graph\Environment;
use Maestro\Package\Package;

class PackagistSurveyor implements Surveyor
{
    /**
     * @var Packagist
     */
    private $packagist;

    public function __construct(Packagist $packagist)
    {
        $this->packagist = $packagist;
    }

    /**
     * {@inheritDoc}
     */
    public function survey(Environment $environment): Promise
    {
        $workspace = $environment->workspace();
        $package = $environment->vars()->get('package');
        assert($package instanceof Package);

        return $this->packagist->packageInfo($package->name());
    }
}
