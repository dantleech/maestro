<?php

namespace Maestro\Tests\Unit\Library\Workspace;

use Maestro\Tests\IntegrationTestCase;
use Maestro\Library\Workspace\Workspace;

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
            (new Workspace($this->workspace()->path('/'), 'test'))->absolutePath($relative)
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

    public function testCanBePurged()
    {
        $workspace = new Workspace($this->workspace()->path('/'), 'test');
        $this->workspace()->put('goodday/foobar', 'Hello');
        $this->assertFileExists($this->workspace()->path('/goodday/foobar'));
        $workspace->purge();
        $this->assertFileNotExists($this->workspace()->path('/goodday/foobar'));
    }

    public function testReturnsName()
    {
        $workspace = new Workspace('path', 'name');
        $this->assertEquals('name', $workspace->name());
    }
}
