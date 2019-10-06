<?php

namespace Maestro\Tests\Unit\Extension\Vcs\Survey;

use Maestro\Extension\Vcs\Survey\VersionResult;
use Maestro\Extension\Vcs\Survey\VersionSurveyor;
use Maestro\Library\Support\Package\Package;
use Maestro\Library\Workspace\Workspace;
use Maestro\Tests\IntegrationTestCase;
use Maestro\Library\Git\GitRepositoryFactory;

class VersionSurveryorTest extends IntegrationTestCase
{
    const PACKAGE_NAME = 'foobar';

    private $git;

    /**
     * @var VersionSurveyor
     */
    private $surveyor;

    /**
     * @var Package
     */
    private $package;

    /**
     * @var Workspace
     */
    private $workspace;

    protected function setUp(): void
    {
        $this->workspace()->reset();
        $this->initPackage(self::PACKAGE_NAME);
        $this->git = $this->container([
        ])->get(GitRepositoryFactory::class);
        $this->package = new Package(self::PACKAGE_NAME, '1.0.0');
        $this->workspace = new Workspace($this->workspace()->path(self::PACKAGE_NAME), self::PACKAGE_NAME);

        $this->surveyor = new VersionSurveyor($this->git);
    }

    public function testGathersInfoWithNoTags()
    {
        $versionReport = \Amp\Promise\wait($this->surveyor->__invoke($this->package, $this->workspace));

        $this->assertInstanceOf(VersionResult::class, $versionReport);
        $this->assertNull($versionReport->mostRecentTagName());
        $this->assertNull($versionReport->mostRecentTagCommitId());
    }

    public function testGathersWithTags()
    {
        $this->execPackageCommand(self::PACKAGE_NAME, 'git tag 1.0.0');
        $configuredTag = '1.0.0';
        $versionReport = \Amp\Promise\wait($this->surveyor->__invoke($this->package, $this->workspace));

        $this->assertInstanceOf(VersionResult::class, $versionReport);
        $this->assertCount(0, $versionReport->commitsBetween());
        $this->assertEquals($configuredTag, $versionReport->configuredVersion());
        $this->assertEquals(0, $versionReport->divergence());
        $this->assertLikeCommitId($versionReport->headId());
        $this->assertEquals(self::GIT_INITIAL_MESSAGE, $versionReport->headComment());
        $this->assertEquals(self::PACKAGE_NAME, $versionReport->packageName());
        $this->assertLikeCommitId($versionReport->mostRecentTagCommitId());
        $this->assertEquals('1.0.0', $versionReport->mostRecentTagName());
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
        $versionReport = \Amp\Promise\wait($this->surveyor->__invoke($this->package, $this->workspace));

        $this->assertInstanceOf(VersionResult::class, $versionReport);
        $this->assertCount(1, $versionReport->commitsBetween());
        $this->assertEquals($configuredTag, $versionReport->configuredVersion());
        $this->assertEquals(1, $versionReport->divergence());
        $this->assertEquals('another commit', $versionReport->headComment());
        $this->assertEquals(self::PACKAGE_NAME, $versionReport->packageName());
        $this->assertLikeCommitId($versionReport->mostRecentTagCommitId());
        $this->assertEquals('1.0.0', $versionReport->mostRecentTagName());
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
        $package = new Package('one/two', $configuredTag);
        $versionReport = \Amp\Promise\wait($this->surveyor->__invoke($package, $this->workspace));

        $this->assertInstanceOf(VersionResult::class, $versionReport);
        $this->assertTrue($versionReport->willBeTagged());
    }

    private function assertLikeCommitId(string $string): void
    {
        $this->assertEquals(40, strlen($string), 'String looks like a git ID');
    }
}
