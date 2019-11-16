<?php

namespace Maestro\Tests\Unit\Extension\Runner\Model\Loader\Processor;

use Maestro\Extension\Runner\Model\Loader\Processor\NameNormalizingProcessor;
use PHPUnit\Framework\TestCase;

class NameNormalizingProcessorTest extends TestCase
{
    public function testConvertsNameKeyToNodeKey()
    {
        self::assertEquals([
            'nodes' => [
                'foobar' => [],
            ],
        ], NameNormalizingProcessor::forNodes()->process([
            'nodes' => [
                [
                    'name' => 'foobar',
                ]
            ],
        ]));
    }

    public function testIgnoresNodeWithNoChildNodes()
    {
        self::assertEquals([
            'foobar' => [],
        ], NameNormalizingProcessor::forNodes()->process([
            'foobar' => [],
        ]));
    }

    public function testProcessesDescendantNodes()
    {
        self::assertEquals([
            'nodes' => [
                'foobar' => [
                    'nodes' => [
                        'foobar' => [],
                    ],
                ],
            ],
        ], NameNormalizingProcessor::forNodes()->process([
            'nodes' => [
                [
                    'name' => 'foobar',
                    'nodes' => [
                        [
                            'name' => 'foobar',
                        ]
                    ],
                ]
            ],
        ]));
    }

    public function testProcessesPrototypes()
    {
        self::assertEquals([
            'nodes' => [
            ],
            'prototypes' => [
                'foobar' => [
                ],
                'barfoo' => [
                ],
            ],
        ], NameNormalizingProcessor::forPrototypes()->process([
            'nodes' => [
            ],
            'prototypes' => [
                [
                    'name' => 'foobar',
                ],
                [
                    'name' => 'barfoo',
                ],
            ],
        ]));
    }
}
