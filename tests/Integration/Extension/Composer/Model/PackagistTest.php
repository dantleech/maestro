<?php

namespace Maestro\Tests\Integration\Extension\Composer\Model;

use Amp\Artax\DefaultClient;
use Maestro\Extension\Composer\Model\Exception\PackagistDnsError;
use Maestro\Extension\Composer\Model\Packagist;
use Maestro\Extension\Composer\Model\PackagistPackageInfo;
use Maestro\Tests\IntegrationTestCase;

class PackagistTest extends IntegrationTestCase
{
    public function testWithRealHttpRequest(): void
    {
        $defaultClient = new DefaultClient();
        $packagist = new Packagist(
            $defaultClient
        );

        try {
            $info = \Amp\Promise\wait($packagist->packageInfo('symfony/symfony'));
        } catch (PackagistDnsError $e) {
            $this->markTestSkipped($e);
            return;
        }

        $this->assertInstanceOf(PackagistPackageInfo::class, $info);
        $this->assertNotEmpty($info->latestVersion());
    }
}
