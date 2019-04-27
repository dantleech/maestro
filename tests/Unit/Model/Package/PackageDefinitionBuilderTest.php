<?php

namespace Maestro\Tests\Unit\Model\Package;

use Maestro\Model\Package\Exception\InvalidPackageDefinition;
use Maestro\Model\Package\PackageDefinitionBuilder;
use PHPUnit\Framework\TestCase;

class PackageDefinitionBuilderTest extends TestCase
{
    const EXAMPLE_URL = 'https://foobar.com';

    public function testFromArray()
    {
        $package = PackageDefinitionBuilder::createFromArray('foobar/barfoo', [
            'url' => self::EXAMPLE_URL,
            'initialize' => [
                'foobar',
            ],
        ])->build();


        $this->assertEquals(['foobar'], $package->initCommands());
        $this->assertEquals(self::EXAMPLE_URL, $package->url());
    }

    public function testDefaultsToGithub()
    {
        $package = PackageDefinitionBuilder::create('foobar/barfoo')->build();
        $this->assertEquals('git@github.com:foobar/barfoo', $package->url());
    }

    public function testFromArrayExceptionOnInvalidKeys()
    {
        $this->expectException(InvalidPackageDefinition::class);
        $this->expectExceptionMessage('Unexpected keys "baz", allowed keys: ');
        PackageDefinitionBuilder::createFromArray('foobar/barfoo', [
            'baz' => 'boo',
        ]);
    }
}
