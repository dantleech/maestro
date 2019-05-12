<?php

namespace Maestro\Tests\Unit\Extension;

use Maestro\Extension\NamespaceResolver;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class NamespaceResolverTest extends TestCase
{
    /**
     * @dataProvider provideResolvesNamespace
     */
    public function testResolvesNamespace(string $cwd, string $expectedNamespace, ?string $expectedExceptionMessage = null)
    {
        if ($expectedExceptionMessage) {
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage($expectedExceptionMessage);
        }
        $this->assertEquals($expectedNamespace, (new NamespaceResolver($cwd))->resolve());
    }

    public function provideResolvesNamespace()
    {
        yield 'path1' => [
            '/home/daniel/www/foobar/bar-foo',
            'dd51853d90-bar-foo',
        ];
        yield 'path2' => [
            '/home/daniel/www/foobar/bar-foo1',
            'e179cc27cb-bar-foo1',
        ];
        yield 'empty path' => [
            '',
            'e179cc27cb-bar-foo',
            'The given working directory path was empty'
        ];
    }
}
