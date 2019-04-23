<?php

namespace Maestro\Tests\Unit\Model\Package;

use Maestro\Model\Package\PackageDefinitions;
use PHPUnit\Framework\TestCase;

class PackageDefinitionsTest extends TestCase
{
    public function testFromArray()
    {
        $definitions = PackageDefinitions::fromArray([
            'foobar/barfoo' => [],
            'foobar/foobar' => [],
        ]);
        $this->assertInstanceOf(PackageDefinitions::class, $definitions);
        $this->assertCount(2, $definitions);
    }

    /**
     * @dataProvider provideFilter
     */
    public function testFilter(array $packageNames, ?string $query, array $expectedPackages)
    {
        $definitions = PackageDefinitions::fromArray(
            array_combine(
                $packageNames,
                array_fill(0, count($packageNames), [])
            )
        );
        $definitions = $definitions->query($query);

        $this->assertEquals($definitions->names(), $expectedPackages);
    }

    public function provideFilter()
    {
        yield 'null is the same as all' => [
            [ 'foobar/fo', 'barfoo/bo' ],
            null,
            [ 'foobar/fo', 'barfoo/bo' ],
        ];

        yield 'empty is the same as all' => [
            [ 'foobar/fo', 'barfoo/bo' ],
            '',
            [ 'foobar/fo', 'barfoo/bo' ],
        ];
 
        yield 'exact string' => [
            [ 'foobar/fo', 'barfoo/bo' ],
            'foobar/fo',
            [ 'foobar/fo' ],
        ];
 
        yield 'wildcard * 1' => [
            [ 'foobar/ba', 'foobar/fo', 'barfoo/bo' ],
            'foobar/*',
            [ 'foobar/ba', 'foobar/fo' ],
        ];
 
        yield 'wildcard * 2' => [
            [ 'foobar/ba', 'foobar/fofoo', 'barfoo/bo' ],
            'foobar/*foo',
            [ 'foobar/fofoo' ],
        ];
   }
}
