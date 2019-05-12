<?php

namespace Maestro\Tests\Unit\Model\Package;

use Maestro\Model\Package\PackageDefinitions;
use Maestro\Model\Package\PackageDefinitionsLoader;
use PHPUnit\Framework\TestCase;

class PackageDefinitionsLoaderTest extends TestCase
{
    public function testLoadsPackageDefinitions()
    {
        $definitions = $this->createLoader([])->load([
            'foobar/barfoo' => [
            ]
        ]);
        $this->assertInstanceOf(PackageDefinitions::class, $definitions);
        $this->assertCount(1, $definitions);
    }

    public function testMergesPrototypes()
    {
        $definitions = $this->createLoader([
            'hello' => [
                'parameters' => [ 'foo' ]
            ]
        ])->load([
            'foobar/barfoo' => [
                'prototype' => 'hello',
            ]
        ]);
        $this->assertInstanceOf(PackageDefinitions::class, $definitions);
        $this->assertCount(1, $definitions);
        $this->assertEquals(['foo'], $definitions->get('foobar/barfoo')->parameters());
    }

    public function testPackageDefinitionHasPriotityOverPrototype()
    {
        $definitions = $this->createLoader([
            'hello' => [
                'parameters' => [ 'foo' ],
                'manifest' => [
                    'bar' => [
                        'type' => 'template',
                        'parameters' => [
                            'from' => 'boo'
                        ]
                    ],
                    'foo' => []
                ],
            ]
        ])->load([
            'foobar/barfoo' => [
                'prototype' => 'hello',
                'manifest' => [
                    'bar' => [
                        'type' => 'template',
                        'parameters' => [
                            'from' => 'baz'
                        ]
                    ],
                ]
            ]
        ]);
        $this->assertInstanceOf(PackageDefinitions::class, $definitions);
        $this->assertCount(1, $definitions);
        $this->assertEquals([
            'from'=>'baz',
        ], $definitions->get('foobar/barfoo')->manifest()->get('bar')->parameters());
    }

    protected function createLoader(array $prototypes): PackageDefinitionsLoader
    {
        return new PackageDefinitionsLoader($prototypes);
    }
}
