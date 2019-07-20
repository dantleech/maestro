<?php

namespace Maestro\Tests\Integration\Extension\Maestro\Task;

use Maestro\Extension\Maestro\Task\JsonFileHandler;
use Maestro\Extension\Maestro\Task\JsonFileTask;
use Maestro\Node\Test\HandlerTester;
use Maestro\Tests\IntegrationTestCase;
use Maestro\Workspace\Workspace;
use function Safe\json_decode;
use function Safe\json_encode;
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
    public function testJsonFileHandler(array $config, ?array $existingData, array $expectedData)
    {
        if (null !== $existingData) {
            file_put_contents($this->packageWorkspace->absolutePath($config['targetPath']), json_encode($existingData, JSON_PRETTY_PRINT));
        }
        $environment = HandlerTester::create(new JsonFileHandler())->handle(JsonFileTask::class, $config, [
            'vars' => [
                'manifest.dir' => $this->workspace()->path('/'),
            ],
            'workspace' => $this->packageWorkspace,
        ]);
        $this->assertEquals($expectedData, json_decode(
            file_get_contents($this->packageWorkspace->absolutePath($config['targetPath'])),
            true
        ));
    }

    public function provideJsonFileHandler()
    {
        yield 'create new file' => [
            [
                'targetPath' => 'composer.json',
                'merge' => [
                    'require' => [
                        'composer.json'
                    ],
                ]
            ],
            null,
            [
                'require' => [
                    'composer.json',
                ],
            ]
        ];

        yield 'merge with existing' => [
            [
                'targetPath' => 'composer.json',
                'merge' => [
                    'require' => [
                        'someother' => '1.0.0',
                        'mypackage' => '2.0.0',
                    ],
                ]
            ],
            [
                'name' => 'example',
                'require' => [
                    'someother' => '1.0.0',
                    'mypackage' => '1.0.0',
                ],
            ],
            [
                'name' => 'example',
                'require' => [
                    'someother' => '1.0.0',
                    'mypackage' => '2.0.0',
                ],
            ],
        ];
    }
}
