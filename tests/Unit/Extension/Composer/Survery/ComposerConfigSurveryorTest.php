<?php

namespace Maestro\Tests\Unit\Extension\Composer\Survery;

use Maestro\Extension\Composer\Survery\ComposerConfigSurveryor;
use Maestro\Library\Workspace\Workspace;
use Maestro\Tests\IntegrationTestCase;

class ComposerConfigSurveryorTest extends IntegrationTestCase
{
    /**
     * @var ComposerConfigSurveryor
     */
    private $surveyor;

    protected function setUp(): void
    {
        $this->workspace()->reset();
        $this->surveyor = new ComposerConfigSurveryor();
    }

    public function testNoSurveyWhenNoComposerJson()
    {
        $workspace = new Workspace($this->workspace()->path('/'), 'default');
        $result = \Amp\Promise\wait($this->surveyor->__invoke($workspace));
        $this->assertNull($result);
    }

    public function testGeneratesSurveyResult()
    {
        $this->workspace()->put(
            'composer.json',
            <<<'EOT'
{
    "name": "dantleech/maestro",
    "extra": {
        "branch-alias": {
            "dev-master": "0.1.x-dev"
        }
    }
}
EOT
        );

        $workspace = new Workspace($this->workspace()->path('/'), 'default');
        $result = \Amp\Promise\wait($this->surveyor->__invoke($workspace));
        $this->assertEquals('0.1.x-dev', $result->branchAlias());
    }
}
