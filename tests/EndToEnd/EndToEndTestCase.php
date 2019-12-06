<?php

namespace Maestro\Tests\EndToEnd;

use Maestro\Tests\IntegrationTestCase;
use Symfony\Component\Process\Process;

class EndToEndTestCase extends IntegrationTestCase
{
    protected function command(string $command, string $workspaceDir = null): Process
    {
        $process = Process::fromShellCommandline($cmd = sprintf(
            __DIR__ . '/../../bin/maestro -vvv --workspace-dir=%s %s ',
            $workspaceDir ?? $this->workspace()->path('/workspace'),
            $command
        ), $this->workspace()->path('/'));
        $process->run();

        return $process;
    }

    protected function assertProcessSuccess(Process $process)
    {
        if ($process->getExitCode() === 0) {
            $this->addToAssertionCount(1);
            return;
        }

        $this->fail(sprintf(
            'Process failed with "%s": %s%s',
            $process->getExitCode(),
            $process->getOutput(),
            $process->getErrorOutput()
        ));
    }

    protected function assertProcessFailure(Process $process)
    {
        if ($process->getExitCode() !== 0) {
            $this->addToAssertionCount(1);
            return;
        }

        $this->fail(sprintf(
            'Process succeeded, but it should have failed: %s%s',
            $process->getOutput(),
            $process->getErrorOutput()
        ));
    }
}
