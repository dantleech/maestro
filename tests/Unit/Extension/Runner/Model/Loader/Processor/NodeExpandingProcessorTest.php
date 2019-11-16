<?php

namespace Maestro\Tests\Unit\Extension\Runner\Model\Loader\Processor;

use Maestro\Extension\Runner\Model\Loader\Exception\PrototypeNotFound;
use Maestro\Extension\Runner\Model\Loader\Processor\NodeExpandingProcessor;
use Maestro\Extension\Runner\Model\Loader\Processor\PrototypeExpandingProcessor;
use PHPUnit\Framework\TestCase;

class NodeExpandingProcessorTest extends TestCase
{
    /**
     * @var PrototypeExpandingProcessor
     */
    private $processor;

    protected function setUp(): void
    {
        $this->processor = new NodeExpandingProcessor();
    }

    public function testConvertsNameKeyToNodeKey()
    {
        self::assertEquals([
            'nodes' => [
                'foobar' => [],
            ],
        ], $this->processor->process([
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
        ], $this->processor->process([
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
        ], $this->processor->process([
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

}
