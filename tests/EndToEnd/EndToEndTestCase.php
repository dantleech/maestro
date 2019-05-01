<?php

namespace Maestro\Tests\EndToEnd;

use PHPUnit\Framework\TestCase;
use Phpactor\TestUtils\Workspace;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Webmozart\PathUtil\Path;

class EndToEndTestCase extends TestCase
{
    protected function initWorkspace()
    {
        $this->workspace()->reset();
    }

    protected function command(string $command): Process
    {
        $process = new Process(sprintf(
            __DIR__ . '/../../bin/maestro %s',
            $command
        ), $this->workspace()->path('/'));
        $process->run();

        return $process;
    }

    protected function packageUrl(string $name)
    {
        return $this->workspace()->path('/'.$name);
    }

    protected function initPackage(string $name)
    {
        $filesystem = new Filesystem();
        $filesystem->mirror(__DIR__ . '/../Project/one', $this->packageUrl($name));

        foreach ([
            'git init',
            'git add *',
            'git commit -m "test'
        ] as $command) {
            $process = new Process(sprintf(
                'git init'
                ), $this->packageUrl($name));
            $process->mustRun();
        }
    }


    protected function workspace(): Workspace
    {
        return Workspace::create(__DIR__ . '/../Workspace');
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

    protected function packageWorkspacePath(string $subPath = ''): string
    {
        return $this->workspace()->path(Path::join(['Workspace', $subPath]));
    }

    protected function saveConfig(array $config)
    {
        $config['workspace_path'] = $this->packageWorkspacePath();
        file_put_contents($this->workspace()->path('maestro.json'), json_encode($config));
    }
}
