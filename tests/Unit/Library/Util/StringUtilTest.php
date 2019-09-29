<?php

namespace Maestro\Tests\Unit\Library\Util;

use Maestro\Library\Util\StringUtil;
use PHPUnit\Framework\TestCase;

class StringUtilTest extends TestCase
{
    /**
     * @dataProvider provideFirstLine
     */
    public function testFirstLine(string $input, string $expected)
    {
        $this->assertEquals($expected, StringUtil::firstLine($input));
    }

    public function provideFirstLine()
    {
        yield [
            '',
            ''
        ];
    }
}
