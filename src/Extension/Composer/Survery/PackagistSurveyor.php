<?php

namespace Maestro\Extension\Composer\Survery;

use Amp\Promise;
use Amp\Success;
use Maestro\Library\Composer\Exception\PackagistError;
use Maestro\Library\Composer\Packagist;
use Maestro\Library\Composer\PackagistPackageInfo;
use Maestro\Extension\Survey\Model\Surveyor;
use Maestro\Graph\Environment;
use Maestro\Package\Package;
use Psr\Log\LoggerInterface;

class PackagistSurveyor implements Surveyor
{
    /**
     * @var Packagist
     */
    private $packagist;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(Packagist $packagist, LoggerInterface $logger)
    {
        $this->packagist = $packagist;
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function survey(Environment $environment): Promise
    {
        $workspace = $environment->workspace();
        $package = $environment->vars()->get('package');
        assert($package instanceof Package);

        return \Amp\call(function () use ($package) {
            try {
                return yield $this->packagist->packageInfo($package->name());
            } catch (PackagistError $error) {
                $this->logger->warning(sprintf(
                    'Packagist had a problem, using empty packagist data: "%s"',
                    $error->getMessage()
                ));
                return new Success(
                    new PackagistPackageInfo($package->name())
                );
            }
        });
    }
}
