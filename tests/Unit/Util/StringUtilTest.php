<?php

namespace Maestro\Tests\Unit\Util;

use Maestro\Util\StringUtil;
use PHPUnit\Framework\TestCase;

class StringUtilTest extends TestCase
{
    /**
     * @dataProvider provideLastLine
     */
    public function testLastLine(string $input, string $output)
    {
        self::assertEquals($output, StringUtil::lastLine($input));
    }

    public function provideLastLine()
    {
        yield 'no new line' => [
            'foobar',
            'foobar'
        ];

        yield 'unix new line' => [
            "foobar\nbarfoo",
            'barfoo'
        ];

        yield 'multiple unix new lines' => [
            "foobar\nbarfoo\nnardoo",
            'nardoo'
        ];

        yield 'windows new line' => [
            "foobar\r\nbarfoo\r\nnardoo",
            'nardoo'
        ];

        yield 'mac new line' => [
            "foobar\rbarfoo\rnardoo",
            'nardoo'
        ];

        yield 'phpunit' => [
            <<<'EOT'
PHPUnit 7.5.9-5-gc14c30d15 by Sebastian Bergmann and contributors.

.Generated autoload files containing 0 classes
..............................................................  63 / 148 ( 42%)
............................................................... 126 / 148 ( 85%)
......................                                          148 / 148 (100%)

Time: 2.08 seconds, Memory: 18.00 MB

OK (148 tests, 165 assertions)
EOT
          , 'OK (148 tests, 165 assertions)'
          ];

        yield 'package versions' => [
            <<<'EOT'

ocramius/package-versions:  Generating version class...

ocramius/package-versions: ...done generating version class

EOT
          , 'ocramius/package-versions: ...done generating version class'
          ];
    }

    /**
     * @dataProvider provideFirstLine
     */
    public function testFirstLine(string $input, string $output)
    {
        self::assertEquals($output, StringUtil::firstLine($input));
    }

    public function provideFirstLine()
    {
        yield 'no new line' => [
            'foobar',
            'foobar'
        ];

        yield 'new line' => [
            "Foobar\nbarfoo",
            'barfoo'
        ];
    }
}
