<?php

namespace Maestro\Tests\Unit\Extension\Runner\Model\Loader\Processor;

use Maestro\Extension\Runner\Model\Loader\Processor;
use Maestro\Extension\Runner\Model\Loader\Processor\VariableReplacingProcessor;
use Maestro\Library\TokenReplacer\TokenReplacer;
use PHPUnit\Framework\TestCase;

class VariableReplacingProcessorTest extends TestCase
{
    public function testNoVarsOrArgs()
    {
        self::assertEquals([], $this->create()->process([]));
    }

    public function testRelaceArgs()
    {
        self::assertEquals([
            'vars' => [
                'var' => 'bar',
            ],
            'args' => [
                'foo' => 'bar',
            ],
        ], $this->create()->process([
            'vars' => [
                'var' => 'bar',
            ],
            'args' => [
                'foo' => '%var%',
            ],
        ]));
    }

    public function testReplaceVars()
    {
        self::assertEquals([
            'vars' => [
                'var' => 'bar',
                'bar' => 'bar',
            ]
        ], $this->create()->process([
            'vars' => [
                'var' => 'bar',
                'bar' => '%var%',
            ]
        ]));
    }

    public function testReplaceVarsNested()
    {
        self::assertEquals([
            'vars' => [
                'bar' => 'bar',
            ],
            'nodes' => [
                'one' => [
                    'vars' => [
                        'packageName' => 'bar',
                    ],
                    'args' => [
                        'foo' => 'bar',
                    ],
                ],
            ],
        ], $this->create()->process([
            'vars' => [
                'bar' => 'bar',
            ],
            'nodes' => [
                'one' => [
                    'vars' => [
                        'packageName' => '%bar%',
                    ],
                    'args' => [
                        'foo' => '%packageName%',
                    ],
                ],
            ],
        ]));
    }

    public function testArrayValuesReplace()
    {
        self::assertEquals([
            'vars' => [
                'var' => ['bar'],
            ],
            'args' => [
                'foo' => ['bar'],
            ],
        ], $this->create()->process([
            'vars' => [
                'var' => ['bar'],
            ],
            'args' => [
                'foo' => '%var%',
            ],
        ]));
    }

    public function testRelaceNestedArgValues()
    {
        self::assertEquals([
            'vars' => [
                'var' => 'bar',
            ],
            'args' => [
                'foo' => [
                    'bar' => 'bar',
                ],
            ],
        ], $this->create()->process([
            'vars' => [
                'var' => 'bar',
            ],
            'args' => [
                'foo' => [
                    'bar' => '%var%',
                ],
            ],
        ]));
    }

    public function testSubNodeVars()
    {
        self::assertEquals([
            'vars' => [
                'var' => 'bar',
            ],
            'nodes' => [
                'one' => [
                    'args' => [
                        'bar' => 'bar',
                    ],
                ],
            ],
        ], $this->create()->process([
            'vars' => [
                'var' => 'bar',
            ],
            'nodes' => [
                'one' => [
                    'args' => [
                        'bar' => '%var%',
                    ],
                ],
            ],
        ]));
    }

    public function testMagicallyIncludesNodeName()
    {
        self::assertEquals([
            'nodes' => [
                'one' => [
                    'args' => [
                        'bar' => 'one',
                    ],
                ],
            ],
        ], $this->create()->process([
            'nodes' => [
                'one' => [
                    'args' => [
                        'bar' => '%_name%',
                    ],
                ],
            ],
        ]));
    }

    public function testDoesNotConvertBoolToString()
    {
        self::assertEquals([
            'nodes' => [
                'one' => [
                    'args' => [
                        'bar' => true,
                    ],
                ],
            ],
        ], $this->create()->process([
            'nodes' => [
                'one' => [
                    'args' => [
                        'bar' => true,
                    ],
                ],
            ],
        ]));
    }

    private function create(): Processor
    {
        return new VariableReplacingProcessor(new TokenReplacer());
    }
}
