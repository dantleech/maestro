<?php

namespace Maestro\Tests\Unit\Loader\Processor;

use Maestro\Loader\Exception\PrototypeNotFound;
use Maestro\Loader\Processor\PrototypeExpandingProcessor;
use PHPUnit\Framework\TestCase;

class PrototypeExpandingProcessorTest extends TestCase
{
    /**
     * @var PrototypeExpandingProcessor
     */
    private $processor;

    protected function setUp(): void
    {
        $this->processor = new PrototypeExpandingProcessor();
    }

    public function testIgnoresManifestWithNoPrototypes()
    {
        $manifest = [
            'packages' => [
                'foobar' => 'barfoo',
            ],
        ];

        $this->assertEquals($manifest, $this->processor->process($manifest));
    }

    public function testRemovesPrototypesKey()
    {
        $manifest = [
            'prototypes' => [
                'foobar' => [
                    'tasks' => [
                    ]
                ],
            ],
            'packages' => [
                'foobar' => 'barfoo',
            ],
        ];

        $this->assertEquals([
            'packages' => [
                'foobar' => 'barfoo',
            ],
        ], $this->processor->process($manifest));
    }

    public function testExpandsPackageWithPrototype()
    {
        $manifest = [
            'prototypes' => [
                'foobar' => [
                    'tasks' => [
                        'hello' => [
                            'type' => 'null'
                        ]
                    ]
                ],
            ],
            'packages' => [
                'foobar' => [
                    'prototype' => 'foobar',
                ],
            ],
        ];

        $this->assertEquals([
            'packages' => [
                'foobar' => [
                    'tasks' => [
                        'hello' => [
                            'type' => 'null',
                        ],
                    ],
                ],
            ],
        ], $this->processor->process($manifest));
    }

    public function testThrowsExceptionIfPrototypeNotFound()
    {
        $this->expectException(PrototypeNotFound::class);
        $this->processor->process([
            'prototypes' => [
                'foobar' => [
                    'tasks' => [
                        'hello' => [
                            'type' => 'null'
                        ]
                    ]
                ],
            ],
            'packages' => [
                'foobar' => [
                    'prototype' => 'barfoo',
                ],
            ],
        ]);
    }
}
