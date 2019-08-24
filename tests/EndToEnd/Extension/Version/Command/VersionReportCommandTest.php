<?php

namespace Maestro\Tests\EndToEnd\Extension\Version\Command;

use Maestro\Tests\EndToEnd\EndToEndTestCase;

class VersionReportCommandTest extends EndToEndTestCase
{
    protected function setUp(): void
    {
        $this->workspace()->reset();
        $this->initPackage('foobar');
    }

    public function testTag()
    {
        $this->createPlan('plan.json', [
            'packages' => [
                'foobar' => [
                    'url' => $this->packagePath('foobar'),
                    'version' => '123.123',
                ],
            ],
        ]);

        $process = $this->command('version:report plan.json');
        $this->assertProcessSuccess($process);
    }
}
