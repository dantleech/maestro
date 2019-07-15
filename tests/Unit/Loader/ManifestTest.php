<?php

namespace Maestro\Tests\Unit\Loader;

use Maestro\Loader\Loader\NullLoader;
use Maestro\Loader\Manifest;
use PHPUnit\Framework\TestCase;

class ManifestTest extends TestCase
{
    public function testLoadsWithParameters()
    {
        $manifest = Manifest::loadFromArray([
            'parameters' => [
                'one' => 'two',
            ],
        ]);

        $this->assertEquals([
            'one' => 'two',
        ], $manifest->parameters());
    }

    public function testLoadsWithLoaders()
    {
        $manifest = Manifest::loadFromArray([
            'packages' => [
                'some-vendor/some-foobar' => [
                    'loaders' => [
                        [
                            'type' => NullLoader::class
                        ],
                    ]
                ],
            ],
        ]);

        $this->assertCount(1, $manifest->packages());
        $this->assertEquals('some-vendor/some-foobar', $manifest->packages()[0]->name());
        $loaders = $manifest->packages()[0]->loaders();
        $this->assertCount(1, $loaders);
    }
}
