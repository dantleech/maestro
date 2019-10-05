<?php

namespace Maestro\Tests\EndToEnd\Extension\Task\Command;

use Maestro\Tests\EndToEnd\EndToEndTestCase;

class DebugTaskCommandTest extends EndToEndTestCase
{
    public function testListAllTaskAlises()
    {
        $process = $this->command('debug:task');
        $this->assertProcessSuccess($process);
        $this->assertStringContainsString('Registered', $process->getOutput());
    }

    public function testShowInformationForSpecificTask()
    {
        $process = $this->command('debug:task null');
        $this->assertProcessSuccess($process);
    }
}
