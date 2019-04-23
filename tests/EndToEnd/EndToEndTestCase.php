<?php

namespace Maestro\Tests\EndToEnd;

use PHPUnit\Framework\TestCase;
use Phpactor\TestUtils\Workspace;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class EndToEndTestCase extends TestCase
{
    protected function initWorkspace()
    {
        $this->workspace()->reset();
    }

    protected function command(string $command): Process
    {
        $process = new Process(sprintf(
            __DIR__ . '/../../bin/maestro %s', $command
        ), $this->workspace()->path('/'));
        $process->run();

        return $process;
    }

    protected function projectPath(string $name)
    {
        return __DIR__ . '/../Example/' . $name;
    }

    protected function workspace(): Workspace
    {
        return Workspace::create(__DIR__ . '/../Workspace');
    }

    protected function assertProcessSuccess(Process $process)
    {
        if ($process->getExitCode() === 0) {
            return;
        }

        $this->fail(sprintf(
            'Process failed with "%s": %s%s',
            $process->getExitCode(),
            $process->getOutput(),
            $process->getErrorOutput()
        ));
    }

    protected function saveConfig(array $config)
    {
        file_put_contents($this->workspace()->path('maestro.json'), json_encode($config));
    }
}
