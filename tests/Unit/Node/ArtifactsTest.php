<?php

namespace Maestro\Tests\Unit\Node;

use Maestro\Node\Environment;
use Maestro\Node\Vars;
use PHPUnit\Framework\TestCase;

class ArtifactsTest extends TestCase
{
    public function testReturnsArtifact()
    {
        $environment = Environment::create([
            'vars' => Vars::fromArray([
                'foo' => 'bar'
            ]),
        ]);
        $this->assertEquals('bar', $environment->vars()->get('foo'));
    }

    public function testHasMethodToDetermineIfArtifactExists()
    {
        $environment = Environment::create([
            'vars' => Vars::fromArray([
                'foo' => 'bar'
            ]),
        ]);
        $this->assertTrue($environment->vars()->has('foo'));
        $this->assertFalse($environment->vars()->has('bar'));
    }

    public function testMergesEnvironment()
    {
        $environment1 = Environment::create([
            'vars' => Vars::fromArray([
                'foo' => 'bar'
            ]),
        ]);
        $environment2 = Environment::create([
            'vars' => Vars::fromArray([
                'foo' => 'doo',
                'bar' => 'foo'
            ]),
        ]);
        $expected = Environment::create([
            'vars' => Vars::fromArray([
                'foo' => 'doo',
                'bar' => 'foo'
            ]),
        ]);

        $this->assertEquals($expected, $environment1->merge($environment2));
    }
}
