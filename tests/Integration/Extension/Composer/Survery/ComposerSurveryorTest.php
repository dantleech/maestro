<?php

namespace Maestro\Tests\Integration\Extension\Composer\Survery;

use Maestro\Extension\Composer\Survery\ComposerSurveryor;
use Maestro\Extension\Version\Survey\PackageResult;
use Maestro\Graph\Environment;
use Maestro\Tests\IntegrationTestCase;
use Maestro\Workspace\Workspace;

class ComposerSurveryorTest extends IntegrationTestCase
{
    private const PACKAGE_NAME = 'foobar_package';

    /**
     * @var ComposerSurveryor
     */
    private $surveyor;

    protected function setUp(): void
    {
        $this->workspace()->reset();
        $this->surveyor = new ComposerSurveryor();
    }

    public function testReturnsEmptyIfComposerNotPresent()
    {
        $packageResult = \Amp\Promise\wait($this->surveyor->survey($this->createEnv()));
        $this->assertNull($packageResult);
    }

    public function testReturnBranchAlias()
    {
        $this->workspace()->put(self::PACKAGE_NAME . '/composer.json', json_encode([
            'extra' => [
                'branch-alias' =>  [
                'dev-master' => '1.0.x-dev'
                ]
            ]
        ]));

        $packageResult = \Amp\Promise\wait($this->surveyor->survey($this->createEnv()));

        $this->assertInstanceOf(PackageResult::class, $packageResult);
        $this->assertEquals('1.0.x-dev', $packageResult->branchAlias());
    }

    private function createEnv($version = null): Environment
    {
        return Environment::create([
            'workspace' => new Workspace($this->packagePath(self::PACKAGE_NAME), 'one'),
        ]);
    }
}
