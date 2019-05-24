<?php

namespace Maestro\Tests\Unit\Workspace;

use Maestro\Tests\IntegrationTestCase;
use Maestro\Workspace\Workspace;
use PHPUnit\Framework\TestCase;

class WorkspaceTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        $this->workspace()->reset();
    }

    /**
     * @dataProvider provideAbsolutePath
     */
    public function testReturnsAbsolutePathForRelativePath(string $relative, string $expected)
    {
        $this->assertEquals(
            rtrim($this->workspace()->path($expected), '/'),
            (new Workspace($this->workspace()->path('/')))->absolutePath($relative)
        );
    }

    public function provideAbsolutePath()
    {
        yield [
            'foobar',
            'foobar',
        ];

        yield [
            '',
            '',
        ];
    }
}
