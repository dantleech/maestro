<?php

namespace Maestro\Tests\EndToEnd\Extension\Git\Command;

use Maestro\Tests\EndToEnd\EndToEndTestCase;

class TagVersionCommandTest extends EndToEndTestCase
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

        $process = $this->command('git:tag plan.json');
        $this->assertProcessSuccess($process);
    }
}
