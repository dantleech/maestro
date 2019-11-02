<?php

namespace Maestro\Tests\Unit\Extension\Runner\Model\Loader\Processor;

use Maestro\Extension\Runner\Model\Loader\Processor;
use Maestro\Extension\Runner\Model\Loader\Processor\VariableReplacingProcessor;
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

    private function create(): Processor
    {
        return new VariableReplacingProcessor();
    }
}
