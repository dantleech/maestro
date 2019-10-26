<?php

namespace Maestro\Tests\Unit\Loader\Processor;

use Maestro\Extension\Runner\Model\Loader\Exception\PrototypeNotFound;
use Maestro\Extension\Runner\Model\Loader\Processor\PrototypeExpandingProcessor;
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
            'nodes' => [
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
            'nodes' => [
                'foobar' => 'barfoo',
            ],
        ];

        $this->assertEquals([
            'nodes' => [
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
            'nodes' => [
                'foobar' => [
                    'prototype' => 'foobar',
                ],
            ],
        ];

        $this->assertEquals([
            'nodes' => [
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

    public function testExpandsNestedNode()
    {
        $manifest = [
            'prototypes' => [
                'foobar' => [
                    'nodes' => [
                        'hello' => [
                            'type' => 'null'
                        ]
                    ]
                ],
            ],
            'nodes' => [
                'foobar' => [
                    'nodes' => [
                        'barfoo' => [
                            'prototype' => 'foobar',
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEquals([
            'nodes' => [
                'foobar' => [
                    'nodes' => [
                        'barfoo' => [
                            'nodes' => [
                                'hello' => [
                                    'type' => 'null',
                                ],
                            ],
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
            'nodes' => [
                'foobar' => [
                    'prototype' => 'barfoo',
                ],
            ],
        ]);
    }

    public function testOverridenValuesAreOverridden()
    {
        $this->assertEquals([
            'nodes' => [
                'foobar' => [
                    'tasks' => [
                        'hello' => [
                            'type' => 'barfoo',
                        ],
                    ],
                ],
            ]
        ], $this->processor->process([
            'prototypes' => [
                'foobar' => [
                    'tasks' => [
                        'hello' => [
                            'type' => 'null'
                        ]
                    ]
                ],
            ],
            'nodes' => [
                'foobar' => [
                    'prototype' => 'foobar',
                    'tasks' => [
                        'hello' => [
                            'type' => 'barfoo'
                        ],
                    ],
                ],
            ],
        ]));
    }
}
