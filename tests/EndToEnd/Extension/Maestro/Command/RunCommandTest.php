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

    public function testRunWithLogging()
    {
        $this->createPlan('plan.json', []);
        $process = $this->command('run plan.json --log-enable --log-level=info');
        $this->assertProcessSuccess($process);
        $this->assertStringContainsString('Built application', $process->getErrorOutput());
    }

    public function testRunWithLogPath()
    {
        $this->createPlan('plan.json', []);
        $process = $this->command('run plan.json --log-enable --log-level=debug --log-path=foobar.log');
        $this->assertProcessSuccess($process);
        $this->assertFileExists($this->workspace()->path('foobar.log'));
    }

    public function testRunWithWorkingDirectory()
    {
        mkdir($this->workspace()->path('/new-workdir'));

        $this->createPlan('new-workdir/plan.json', []);
        $process = $this->command(sprintf(
            'run plan.json --working-dir=%s --log-enable --log-path=maestro.log --log-level=debug',
            $this->workspace()->path('new-workdir')
        ));
        $this->assertProcessSuccess($process);
        $this->assertFileExists($this->workspace()->path('new-workdir/maestro.log'));
    }

    public function testFailIfWorkingDirectoryDoesNotExist()
    {
        $process = $this->command(sprintf(
            'run plan.json --working-dir=%s --log-enable',
            $this->workspace()->path('new-workdir')
        ));
        $this->assertProcessFailure($process);
        $this->assertStringContainsString('Working directory', $process->getErrorOutput());
    }

    public function testRunWithCustomWorkspace()
    {
        $this->createPlan('plan.json', [
            'packages' => [
                'mypackage' => [
                    'tasks' => [
                        'say hello' => [
                            'type' => 'script',
                            'parameters' => [
                                'script' => 'echo "Foobar" > foobar',
                            ]
                        ]
                    ],
                ],
            ],
        ]);
        $process = $this->command(sprintf(
            'run plan.json --workspace-dir=%s --namespace=testnamespace',
            $this->workspace()->path('my-workspace')
        ));
        $this->assertProcessSuccess($process);
        $this->assertFileExists($this->workspace()->path('/my-workspace/testnamespace/mypackage/foobar'));
    }

    public function testDumpsDotFileToStdout()
    {
        $this->createPlan('plan.json', [
        ]);
        $process = $this->command('run plan.json --dot');
        $this->assertProcessSuccess($process);
    }
}
