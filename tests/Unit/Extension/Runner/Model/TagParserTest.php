<?php

namespace Maestro\Tests\Unit\Extension\Runner\Model;

use Maestro\Extension\Runner\Model\TagParser;
use PHPUnit\Framework\TestCase;

class TagParserTest extends TestCase
{
    /**
     * @dataProvider provideParseTags
     */
    public function testParseTags(string $input, array $expectedTags)
    {
        $this->assertEquals($expectedTags, (new TagParser())->parse($input));
    }

    public function provideParseTags()
    {
        yield 'one' => [
            'one',
            ['one']
        ];

        yield 'multiple' => [
            'one,two',
            ['one', 'two']
        ];
        yield 'multiple with spaces' => [
            'one ,  two',
            ['one', 'two']
        ];
    }
}
