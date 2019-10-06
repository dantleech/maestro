<?php

namespace Maestro\Tests\Unit\Extension\Json\Task;

use Maestro\Extension\Json\Task\JsonFileHandler;
use Maestro\Extension\Json\Task\JsonFileTask;
use Maestro\Library\Task\Test\HandlerTester;
use Maestro\Library\Workspace\Workspace;
use Maestro\Tests\IntegrationTestCase;
use function Safe\file_get_contents;
use function Safe\file_put_contents;

class JsonFileHandlerTest extends IntegrationTestCase
{
    /**
     * @var Workspace
     */
    private $packageWorkspace;

    /**
     * @var JsonFileHandler
     */
    private $handler;

    protected function setUp(): void
    {
        $this->workspace()->reset();
        $this->packageWorkspace = new Workspace($this->workspace()->path('/'), 'test');
    }

    /**
     * @dataProvider provideJsonFileHandler
     */
    public function testJsonFileHandler(array $config, ?string $existingData, string $expected)
    {
        if (null !== $existingData) {
            file_put_contents($this->packageWorkspace->absolutePath($config['targetPath']), $existingData);
        }
        $artifacts = HandlerTester::create(new JsonFileHandler())->handle(JsonFileTask::class, $config, [
            $this->packageWorkspace,
        ]);

        $this->assertEquals(
            $expected,
            file_get_contents($this->packageWorkspace->absolutePath($config['targetPath']))
        );
    }

    public function provideJsonFileHandler()
    {
        yield 'create new file' => [
            [
                'targetPath' => 'composer.json',
                'data' => [
                    'require' => [
                        'hello' => 'world',
                    ],
                ]
            ],
            null,
            <<<'EOT'
            {
                "require": {
                    "hello": "world"
                }
            }
            EOT
            ,
        ];

        yield 'data with existing' => [
            [
                'targetPath' => 'composer.json',
                'data' => [
                    'require' => [
                        'someother' => '1.0.0',
                        'mypackage' => '2.0.0',
                    ],
                ]
            ],
            <<<'EOT'
            {
                "name": "example",
                "require": {
                    "someother": "1.0.0",
                    "mypackage": "1.0.0"
                }
            }
            EOT
            ,
            <<<'EOT'
            {
                "name": "example",
                "require": {
                    "someother": "1.0.0",
                    "mypackage": "2.0.0"
                }
            }
            EOT
        ];

        yield 'preserves objects' => [
            [
                'targetPath' => 'composer.json',
                'data' => [
                    'require' => []
                ]
            ],
            <<<'EOT'
            {
                "name": "example",
                "require": {}
            }
            EOT,
            <<<'EOT'
            {
                "name": "example",
                "require": {}
            }
            EOT
        ];

        yield 'preserves nested objects' => [
            [
                'targetPath' => 'composer.json',
                'data' => [
                    'one' => [
                        'two' => [],
                    ]
                ]
            ],
            <<<'EOT'
            {
                "one": {
                    "two": {}
                }
            }
            EOT,
            <<<'EOT'
            {
                "one": {
                    "two": {}
                }
            }
            EOT
        ];
    }
}
