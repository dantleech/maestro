<?php

namespace Maestro\Tests\EndToEnd\Extension\Tmux\Command;

use Maestro\Tests\EndToEnd\EndToEndTestCase;

class TmuxCommandTest extends EndToEndTestCase
{
    public function testFailsOnUnknownWorkspace()
    {
        $process = $this->command('tmux unknown/package');
        $this->assertProcessFailure($process);
        $this->assertStringContainsString('Unknown workspace', $process->getErrorOutput());
    }
}
