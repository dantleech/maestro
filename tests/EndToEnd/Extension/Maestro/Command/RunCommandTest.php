<?php

namespace Maestro\Tests\EndToEnd\Extension\Maestro\Command;

use Maestro\Tests\EndToEnd\EndToEndTestCase;

class RunCommandTest extends EndToEndTestCase
{
    protected function setUp(): void
    {
        $this->workspace()->reset();
        $this->initPackage('foobar');
    }

    public function testErrorIfPlanNotFound()
    {
        $process = $this->command('run plan.json');
        $this->assertProcessFailure($process);
        $this->assertStringContainsString('not exist', $process->getErrorOutput());
    }

    public function testRun()
    {
        $this->createPlan('plan.json', [
        ]);
        $process = $this->command('run plan.json');
        $this->assertProcessSuccess($process);
    }
}
