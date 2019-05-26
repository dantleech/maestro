<?php

namespace Maestro\Tests\Unit\Loader;

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

    public function testLoadsWithPrototypes()
    {
        $manifest = Manifest::loadFromArray([
            'prototypes' => [
                'one' => [
                    'tasks' => [
                        [
                            'type' => 'command',
                            'parameters' => [
                                'command' => 'composer install',
                            ],
                        ],
                    ]
                ],
            ],
        ]);

        $this->assertCount(1, $manifest->prototypes());
        $this->assertEquals('one', $manifest->prototypes()['one']->name());
        $tasks = $manifest->prototypes()['one']->tasks();
        $this->assertCount(1, $tasks);
        $this->assertEquals('command', $tasks[0]->type());
    }

    public function testLoadsWithPackages()
    {
        $manifest = Manifest::loadFromArray([
            'packages' => [
                'some-vendor/some-foobar' => [
                    'prototype' => 'foobar',
                    'tasks' => [
                        [
                            'type' => 'command',
                            'parameters' => [
                                'command' => 'composer install',
                            ],
                        ],
                    ]
                ],
            ],
        ]);

        $this->assertCount(1, $manifest->packages());
        $this->assertEquals('some-vendor/some-foobar', $manifest->packages()[0]->name());
        $this->assertEquals('foobar', $manifest->packages()[0]->prototype());
        $tasks = $manifest->packages()[0]->tasks();
        $this->assertCount(1, $tasks);
        $this->assertEquals('command', $tasks[0]->type());
    }
}
