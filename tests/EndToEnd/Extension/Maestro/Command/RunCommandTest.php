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

    public function testCanLimitConcurrency()
    {
        $this->createPlan('plan.json', [
        ]);
        $process = $this->command('run plan.json --concurrency=5');
        $this->assertProcessSuccess($process);
    }

    public function testVerboseModeLogsDebugMessagesToStderr()
    {
        $this->createPlan('plan.json', [
            'packages' => [
                'mypackage' => [
                    'tasks' => [
                        'hello' => [
                            'type' => 'script',
                            'parameters' => [
                                'script' => 'echo "Hello World"',
                            ]
                        ]
                    ],
                ],
            ],
        ]);
        $process = $this->command('run plan.json -v');
        $this->assertProcessSuccess($process);
        $this->assertStringContainsString('Hello World', $process->getErrorOutput());
    }

    public function testTargetCanBeSpecified()
    {
        $this->createPlan('plan.json', [
            'packages' => [
                'mypackage' => [
                    'tasks' => [
                        'hello' => [
                            'type' => 'script',
                            'parameters' => [
                                'script' => 'echo "Hello World"',
                            ]
                        ],
                        'goodbye' => [
                            'type' => 'script',
                            'parameters' => [
                                'script' => 'echo "Goodbye World"',
                            ]
                        ]
                    ],
                ],
            ],
        ]);
        $process = $this->command('run plan.json "mypackage/goodbye" -v');
        $this->assertProcessSuccess($process);
        $this->assertStringNotContainsString('Hello World', $process->getErrorOutput());
        $this->assertStringContainsString('Goodbye World', $process->getErrorOutput());
    }

    public function testExitsWithNumberOfFailedTasksAsCode()
    {
        $this->createPlan('plan.json', [
            'packages' => [
                'mypackage' => [
                    'tasks' => [
                        'hello' => [
                            'type' => 'script',
                            'parameters' => [
                                'script' => 'exit 1',
                            ]
                        ],
                        'goodbye' => [
                            'type' => 'script',
                            'parameters' => [
                                'script' => 'exit 1',
                            ]
                        ],
                        'foobar' => [
                            'type' => 'script',
                            'parameters' => [
                                'script' => 'exit 0',
                            ]
                        ]
                    ],
                ],
            ],
        ]);
        $process = $this->command('run plan.json -v');
        $this->assertProcessFailure($process);
        $this->assertEquals(2, $process->getExitCode());
    }

    public function testListsTargets()
    {
        $this->createPlan('plan.json', [
            'packages' => [
                'mypackage' => [
                    'tasks' => [
                        'hello' => [
                            'type' => 'null',
                        ],
                        'goodbye' => [
                            'type' => 'null',
                        ],
                        'foobar' => [
                            'type' => 'null',
                        ]
                    ],
                ],
            ],
        ]);
        $process = $this->command('run plan.json --targets');
        $this->assertProcessSuccess($process);
        $this->assertStringContainsString('mypackage/hello', $process->getOutput());
    }

    public function testGraphDepthCanBeSpecified()
    {
        $this->createPlan('plan.json', [
            'packages' => [
                'mypackage' => [
                    'tasks' => [
                        'hello' => [
                            'type' => 'null',
                        ],
                        'goodbye' => [
                            'type' => 'null',
                        ],
                        'foobar' => [
                            'type' => 'null',
                        ]
                    ],
                ],
            ],
        ]);
        $process = $this->command('run plan.json --depth=1 --targets');
        $this->assertProcessSuccess($process);
        $this->assertStringNotContainsString('mypackage/hello', $process->getOutput());
        $this->assertStringContainsString('mypackage', $process->getOutput());
    }

    public function testScriptCanBeExecutedOnLeafNodes()
    {
        $this->createPlan('plan.json', [
            'packages' => [
                'mypackage' => [
                ],
                'foobar' => [
                ],
            ],
        ]);
        $process = $this->command('run plan.json -v --exec="echo \"Hello \"\$PACKAGE_NAME"');
        $this->assertProcessSuccess($process);
        $this->assertStringContainsString('Hello mypackage', $process->getErrorOutput());
        $this->assertStringContainsString('Hello foobar', $process->getErrorOutput());
    }
}
