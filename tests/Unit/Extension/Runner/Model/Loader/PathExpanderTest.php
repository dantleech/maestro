<?php

namespace Maestro\Tests\Unit\Extension\Runner\Model\Loader;

use Maestro\Extension\Runner\Model\Loader\PathExpander;
use PHPUnit\Framework\TestCase;

class PathExpanderTest extends TestCase
{
    /**
     * @dataProvider provideExpandPath
     */
    public function testExpandPath(?string $parentPath, array $paths, array $expectedPaths)
    {
        $this->assertEquals($expectedPaths, (new PathExpander())->expand($paths, $parentPath));
    }

    public function provideExpandPath()
    {
        yield 'root has no parent path' => [
            null,
            ['/'],
            ['/'],
        ];

        yield 'absolute returns self' => [
            '/foobar',
            ['/barfoo'],
            ['/barfoo'],
        ];

        yield 'relative path 1' => [
            '/foobar',
            ['.'],
            ['/foobar'],
        ];

        yield 'relative path 2' => [
            '/foobar',
            ['./barfoo'],
            ['/foobar/barfoo'],
        ];

        yield 'relative path 3' => [
            '/foobar/barfoo',
            ['../../'],
            ['/'],
        ];

        yield 'relative path 4' => [
            '/foobar/barfoo',
            ['../../../'],
            ['/'],
        ];

        yield 'non-absolute root path' => [
            null,
            ['foobar'],
            ['/foobar'],
        ];
    }
}
