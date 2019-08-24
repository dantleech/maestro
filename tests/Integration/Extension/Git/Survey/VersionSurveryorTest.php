<?php

namespace Maestro\Tests\Integration\Extension\Git\Survey;

use Maestro\Extension\Git\Model\Git;
use Maestro\Extension\Git\Model\VersionReport;
use Maestro\Extension\Git\Survey\VersionSurveyor;
use Maestro\Graph\Environment;
use Maestro\Graph\Vars;
use Maestro\Package\Package;
use Maestro\Tests\IntegrationTestCase;
use Maestro\Workspace\Workspace;

class VersionSurveryorTest extends IntegrationTestCase
{
    const PACKAGE_NAME = 'foobar';

    /**
     * @var Git
     */
    private $git;

    /**
     * @var VersionSurveyor
     */
    private $surveyor;

    protected function setUp(): void
    {
        $this->workspace()->reset();
        $this->initPackage(self::PACKAGE_NAME);
        $this->git = $this->container()->get(Git::class);
        $this->surveyor = new VersionSurveyor($this->git);
    }

    public function testGathersInfoWithNoTags()
    {
        $versionReport = \Amp\Promise\wait($this->surveyor->survey($this->createEnv()));

        $this->assertInstanceOf(VersionReport::class, $versionReport);
        $this->assertNull($versionReport->taggedVersion());
        $this->assertNull($versionReport->taggedCommit());
    }

    public function testGathersWithTags()
    {
        $this->execPackageCommand(self::PACKAGE_NAME, 'git tag 1.0.0');
        $configuredTag = '1.0.0';
        $versionReport = \Amp\Promise\wait($this->surveyor->survey($this->createEnvWithPackageVersion($configuredTag)));

        $this->assertInstanceOf(VersionReport::class, $versionReport);
        $this->assertCount(0, $versionReport->commitsBetween());
        $this->assertEquals($configuredTag, $versionReport->configuredVersion());
        $this->assertEquals(0, $versionReport->divergence());
        $this->assertLikeCommitId($versionReport->headCommit());
        $this->assertEquals(self::GIT_INITIAL_MESSAGE, $versionReport->headMessage());
        $this->assertEquals(self::PACKAGE_NAME, $versionReport->packageName());
        $this->assertLikeCommitId($versionReport->taggedCommit());
        $this->assertEquals('1.0.0', $versionReport->taggedVersion());
        $this->assertTrue($versionReport->tagIsMostRecentCommit());
        $this->assertFalse($versionReport->willBeTagged());
    }

    public function testRepoWithCommitsAfterTags()
    {
        $this->execPackageCommand(self::PACKAGE_NAME, 'git tag 1.0.0');
        $this->execPackageCommand(self::PACKAGE_NAME, 'touch Foobar');
        $this->execPackageCommand(self::PACKAGE_NAME, 'git add Foobar');
        $this->execPackageCommand(self::PACKAGE_NAME, 'git commit -m "another commit"');

        $configuredTag = '1.0.0';
        $versionReport = \Amp\Promise\wait($this->surveyor->survey($this->createEnvWithPackageVersion($configuredTag)));

        $this->assertInstanceOf(VersionReport::class, $versionReport);
        $this->assertCount(1, $versionReport->commitsBetween());
        $this->assertEquals($configuredTag, $versionReport->configuredVersion());
        $this->assertEquals(1, $versionReport->divergence());
        $this->assertEquals('another commit', $versionReport->headMessage());
        $this->assertEquals(self::PACKAGE_NAME, $versionReport->packageName());
        $this->assertLikeCommitId($versionReport->taggedCommit());
        $this->assertEquals('1.0.0', $versionReport->taggedVersion());
        $this->assertFalse($versionReport->tagIsMostRecentCommit());
        $this->assertFalse($versionReport->willBeTagged());
    }

    public function testIndicatesThatConfiguredTagIsAheadOfCurrentTag()
    {
        $this->execPackageCommand(self::PACKAGE_NAME, 'git tag 1.0.0');
        $this->execPackageCommand(self::PACKAGE_NAME, 'touch Foobar');
        $this->execPackageCommand(self::PACKAGE_NAME, 'git add Foobar');
        $this->execPackageCommand(self::PACKAGE_NAME, 'git commit -m "another commit"');

        $configuredTag = '1.0.1';
        $versionReport = \Amp\Promise\wait($this->surveyor->survey($this->createEnvWithPackageVersion($configuredTag)));

        $this->assertInstanceOf(VersionReport::class, $versionReport);
        $this->assertTrue($versionReport->willBeTagged());
    }

    private function createEnv($version = null): Environment
    {
        return Environment::create([
            'vars' => Vars::fromArray([
                'package' => new Package(self::PACKAGE_NAME, $version)
            ]),
            'workspace' => new Workspace($this->packagePath(self::PACKAGE_NAME), 'one'),
        ]);
    }

    private function createEnvWithPackageVersion(string $version): Environment
    {
        return $this->createEnv($version);
    }

    private function assertLikeCommitId(string $string): void
    {
        $this->assertEquals(40, strlen($string), 'String looks like a git ID');
    }
}
