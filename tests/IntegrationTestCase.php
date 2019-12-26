<?php

namespace Maestro\Tests;

use Maestro\ApplicationBuilder;
use Maestro\Extension\Runner\RunnerExtension;
use Maestro\Extension\Workspace\WorkspaceExtension;
use PHPUnit\Framework\TestCase;
use Phpactor\Container\Container;
use Phpactor\TestUtils\Workspace;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class IntegrationTestCase extends TestCase
{
    const GIT_INITIAL_MESSAGE = 'test';

    private $workspace;

    protected function setUp(): void
    {
        $this->workspace()->reset();
    }

    public function container(array $config = []): Container
    {
        $config = array_merge([
            RunnerExtension::PARAM_MANIFEST_PATH => $this->workspace()->path('/maestro.json'),
            WorkspaceExtension::PARAM_WORKING_DIRECTORY => $this->workspace()->path('/'),
            WorkspaceExtension::PARAM_WORKSPACE_PATH => $this->workspace()->path('workspace'),
        ], $config);

        return (new ApplicationBuilder)->buildContainer($config);
    }

    public function workspace(): Workspace
    {
        if (!$this->workspace) {
            return $this->workspace = Workspace::create(__DIR__ . '/Workspace');
        }

        return $this->workspace;
    }

    protected function createPlan(string $name, array $data)
    {
        $this->workspace()->put($name, json_encode($data, JSON_PRETTY_PRINT));
    }

    protected function packagePath(string $name)
    {
        return $this->workspace()->path($name);
    }

    protected function initPackage(string $name)
    {
        $filesystem = new Filesystem();
        $filesystem->mirror(__DIR__ . '/Project/one', $this->packagePath($name));

        foreach ([
            'git init',
            'git add *',
            'git commit -m "' . self::GIT_INITIAL_MESSAGE . '"',
        ] as $command) {
            $this->execPackageCommand($name, $command);
        }
    }

    protected function execPackageCommand(string $packageName, string $command): void
    {
        $process = Process::fromShellCommandline(sprintf(
            $command,
        ), $this->packagePath($packageName));
        $process->mustRun();
    }
}
