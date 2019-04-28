<?php

namespace Maestro\Tests\Unit\Model\Package;

use Maestro\Model\Package\PackageDefinitions;
use Maestro\Model\Package\PackageDefinitionsLoader;
use PHPUnit\Framework\TestCase;

class PackageDefinitionsLoaderTest extends TestCase
{
    private $loader;

    protected function setUp(): void
    {
        $this->loader = new PackageDefinitionsLoader();
    }

    public function testLoadsPackageDefinitions()
    {
        $definitions = $this->loader->load([
            'foobar/barfoo' => [
            ]
        ], []);
        $this->assertInstanceOf(PackageDefinitions::class, $definitions);
        $this->assertCount(1, $definitions);
    }

    public function testMergesPrototypes()
    {
        $definitions = $this->loader->load([
            'foobar/barfoo' => [
                'prototype' => 'hello',
            ]
        ], [
            'hello' => [
                'initCommands' => [ 'foo' ]
            ]
        ]);
        $this->assertInstanceOf(PackageDefinitions::class, $definitions);
        $this->assertCount(1, $definitions);
        $this->assertEquals(['foo'], $definitions->get('foobar/barfoo')->initCommands());
    }

    public function testPackageDefinitionHasPriotityOverPrototype()
    {
        $definitions = $this->loader->load([
            'foobar/barfoo' => [
                'prototype' => 'hello',
                'manifest' => [
                    'bar' => [
                        'source' => 'baz'
                    ],
                ]
            ]
        ], [
            'hello' => [
                'initCommands' => [ 'foo' ],
                'manifest' => [
                    'bar' => [
                        'source' => 'boo'
                    ],
                    'foo' => []
                ],
            ]
        ]);
        $this->assertInstanceOf(PackageDefinitions::class, $definitions);
        $this->assertCount(1, $definitions);
        $this->assertEquals('baz', $definitions->get('foobar/barfoo')->manifest()->get('bar')->source());
    }
}
