<?php

namespace Maestro\Tests\Unit\Extension\Composer\Survery;

use Amp\Success;
use Maestro\Extension\Composer\Model\Exception\PackagistError;
use Maestro\Extension\Composer\Model\Packagist;
use Maestro\Extension\Composer\Model\PackagistPackageInfo;
use Maestro\Extension\Composer\Survery\PackagistSurveyor;
use Maestro\Graph\EnvironmentBuilder;
use Maestro\Package\Package;
use Maestro\Workspace\Workspace;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class PackagistSurveyorTest extends TestCase
{
    const EXAMPLE_PACKAGE_NAME = 'foobar';

    /**
     * @var ObjectProphecy
     */
    private $packagist;

    /**
     * @var PackagistSurveyor
     */
    private $surveyor;

    /**
     * @var ObjectProphecy
     */
    private $logger;

    protected function setUp(): void
    {
        $this->packagist = $this->prophesize(Packagist::class);
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->surveyor = new PackagistSurveyor($this->packagist->reveal(), $this->logger->reveal());
    }

    public function testReturnsEmptyPackageInfoIfPackagistHasAnError()
    {
        $this->packagist->packageInfo(self::EXAMPLE_PACKAGE_NAME)->willThrow(
            new PackagistError('sorry')
        );
        $environment = EnvironmentBuilder::create()
            ->withVars([
                'package' => new Package(self::EXAMPLE_PACKAGE_NAME)
            ])
            ->withWorkspace(new Workspace('foo', 'foo'))
            ->build();

        $info = \Amp\Promise\wait($this->surveyor->survey($environment));
        $this->assertInstanceOf(PackagistPackageInfo::class, $info);
        $this->assertEmpty($info->latestVersion());
    }

    public function testReturnsPackagistInfo()
    {
        $this->packagist->packageInfo(self::EXAMPLE_PACKAGE_NAME)->willReturn(
            new Success(new PackagistPackageInfo('foobar', 'latest'))
        );
        $environment = EnvironmentBuilder::create()
            ->withVars([
                'package' => new Package(self::EXAMPLE_PACKAGE_NAME)
            ])
            ->withWorkspace(new Workspace('foo', 'foo'))
            ->build();

        $info = \Amp\Promise\wait($this->surveyor->survey($environment));
        $this->assertInstanceOf(PackagistPackageInfo::class, $info);
        $this->assertEquals('latest', $info->latestVersion());
    }
}
