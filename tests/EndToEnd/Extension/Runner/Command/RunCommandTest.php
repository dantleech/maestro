<?php

namespace Maestro\Tests\EndToEnd\Extension\Runner\Command;

use Maestro\Tests\EndToEnd\EndToEndTestCase;

class RunCommandTest extends EndToEndTestCase
{
    const EXAMPLE_PLAN_NAME = 'maestro.json';

    protected function setUp(): void
    {
        $this->workspace()->reset();
        $this->initPackage('foobar');
    }

    public function testErrorIfPlanNotFound()
    {
        $process = $this->command('run --plan=plan.json');
        $this->assertProcessFailure($process);
        $this->assertStringContainsString('not exist', $process->getErrorOutput());
    }

    public function testRun()
    {
        $this->createPlan(self::EXAMPLE_PLAN_NAME, [
        ]);
        $process = $this->command('run');
        $this->assertProcessSuccess($process);
    }

    public function testRunWithLogging()
    {
        $this->createPlan(self::EXAMPLE_PLAN_NAME, []);
        $process = $this->command('run --log-enable --log-level=info');
        $this->assertProcessSuccess($process);
        $this->assertStringContainsString('Built application', $process->getErrorOutput());
    }

    public function testRunWithLogPath()
    {
        $this->createPlan(self::EXAMPLE_PLAN_NAME, []);
        $process = $this->command('run --log-enable --log-level=debug --log-path=foobar.log');
        $this->assertProcessSuccess($process);
        $this->assertFileExists($this->workspace()->path('foobar.log'));
    }

    public function testRunWithWorkingDirectory()
    {
        mkdir($this->workspace()->path('/new-workdir'));

        $this->createPlan('new-workdir/maestro.json', []);
        $process = $this->command(sprintf(
            'run --working-dir=%s --log-enable --log-path=maestro.log --log-level=debug',
            $this->workspace()->path('new-workdir')
        ));
        $this->assertProcessSuccess($process);
        $this->assertFileExists($this->workspace()->path('new-workdir/maestro.log'));
    }

    public function testFailIfWorkingDirectoryDoesNotExist()
    {
        $process = $this->command(sprintf(
            'run --working-dir=%s --log-enable',
            $this->workspace()->path('new-workdir')
        ));
        $this->assertProcessFailure($process);
        $this->assertStringContainsString('Working directory', $process->getErrorOutput());
    }

    public function testRunWithCustomWorkspaceAndNamespace()
    {
        $this->createPlan(self::EXAMPLE_PLAN_NAME, [
            'packages' => [
                'mypackage' => [
                    'tasks' => [
                        'say hello' => [
                            'type' => 'script',
                            'args' => [
                                'script' => 'echo "Foobar" > foobar',
                            ]
                        ]
                    ],
                ],
            ],
        ]);

        $process = $this->command('run --namespace=testnamespace', 'my-workspace');

        $this->assertProcessSuccess($process);
        $this->assertFileExists($this->workspace()->path('/my-workspace/testnamespace/mypackage/foobar'));
    }

    public function testCanLimitConcurrency()
    {
        $this->createPlan(self::EXAMPLE_PLAN_NAME, [
        ]);
        $process = $this->command('run --concurrency=5');
        $this->assertProcessSuccess($process);
    }

    public function testVerboseModeLogsDebugMessagesToStderr()
    {
        $this->createPlan(self::EXAMPLE_PLAN_NAME, [
            'packages' => [
                'mypackage' => [
                    'tasks' => [
                        'hello' => [
                            'type' => 'script',
                            'args' => [
                                'script' => 'echo "Hello World"',
                            ]
                        ]
                    ],
                ],
            ],
        ]);
        $process = $this->command('run -vvv');
        $this->assertProcessSuccess($process);
        $this->assertStringContainsString('Hello World', $process->getErrorOutput());
    }

    public function testExitsWithNumberOfFailedTasksAsCode()
    {
        $this->createPlan(self::EXAMPLE_PLAN_NAME, [
            'packages' => [
                'mypackage' => [
                    'tasks' => [
                        'hello' => [
                            'type' => 'script',
                            'args' => [
                                'script' => 'exit 1',
                            ]
                        ],
                        'goodbye' => [
                            'type' => 'script',
                            'args' => [
                                'script' => 'exit 1',
                            ]
                        ],
                        'foobar' => [
                            'type' => 'script',
                            'args' => [
                                'script' => 'exit 0',
                            ]
                        ]
                    ],
                ],
            ],
        ]);
        $process = $this->command('run -v');
        $this->assertProcessFailure($process);
        $this->assertEquals(2, $process->getExitCode());
    }

    public function testPurgesWorkspaces()
    {
        $this->createPlan(self::EXAMPLE_PLAN_NAME, [
            'packages' => [
                'mypackage' => [
                ],
                'foobar' => [
                ],
            ],
        ]);

        $this->workspace()->put('workspace/foobar/foobar', 'this-should-not-exist-later');
        $this->command('run --namespace="" --purge');
        $this->assertFileNotExists($this->workspace()->path('workspace/foobar/foobar'));
    }

    public function testRendersReport()
    {
        $this->createPlan(self::EXAMPLE_PLAN_NAME, [
            'packages' => [
                'mypackage' => [
                ],
                'foobar' => [
                ],
            ],
        ]);

        $this->workspace()->put('workspace/foobar/foobar', 'this-should-not-exist-later');
        $process = $this->command('run --report=run --report=run');
        $this->assertStringContainsString('Run Report', $process->getOutput());
    }
}
