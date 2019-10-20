<?php

namespace Maestro\Tests\EndToEnd\Extension\Runner\Command;

use Maestro\Tests\EndToEnd\EndToEndTestCase;

class TaskCommandTest extends EndToEndTestCase
{
    const EXAMPLE_PLAN_NAME = 'maestro.json';

    protected function setUp(): void
    {
        $this->workspace()->reset();
        $this->initPackage('foobar');
    }

    public function testNullTask()
    {
        $this->createPlan(self::EXAMPLE_PLAN_NAME, [
        ]);
        $process = $this->command('task null');
        $this->assertProcessSuccess($process);
    }

    public function testFailsIfNullTaskAnOption()
    {
        $this->createPlan(self::EXAMPLE_PLAN_NAME, [
        ]);
        $process = $this->command('task null --option=no');
        $this->assertProcessFailure($process);
    }

    public function testFailsIfNullTaskHasArgument()
    {
        $this->createPlan(self::EXAMPLE_PLAN_NAME, [
        ]);
        $process = $this->command('task null arg');
        $this->assertProcessFailure($process);
    }

    public function testTemplateTaskWithArguments()
    {
        $this->workspace()->put('source', 'content');
        $this->createPlan(self::EXAMPLE_PLAN_NAME, [
            'nodes' => [
                'package/one' => [
                    'type' => 'package',
                    'args' => [
                        'name' => 'package/one',
                    ],
                ],
            ]
        ]);
        $process = $this->command('task template --path=source --targetPath=dest');
        $this->assertProcessSuccess($process);
    }

    public function testFailIfRequiredOptionMissing()
    {
        $this->createPlan(self::EXAMPLE_PLAN_NAME, [
            'nodes' => [
                'package/one' => [],
            ]
        ]);
        $process = $this->command('task template --targetPath=dest');
        $this->assertProcessFailure($process);
    }
}
