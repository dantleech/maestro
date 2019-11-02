<?php

namespace Maestro\Tests\Unit\Library\TokenReplacer;

use Exception;
use Maestro\Library\TokenReplacer\Exception\InvalidTokenType;
use Maestro\Library\TokenReplacer\Exception\UnknownToken;
use Maestro\Library\TokenReplacer\TokenReplacer;
use PHPUnit\Framework\TestCase;

class TokenReplacerTest extends TestCase
{
    /**
     * @dataProvider provideReplacesTokens
     */
    public function testReplacesTokens(string $input, array $tokens, $expectedValue, Exception $expectedException = null)
    {
        if ($expectedException) {
            $this->expectExceptionObject($expectedException);
        }

        $replacer = new TokenReplacer();

        self::assertEquals($expectedValue, $replacer->replace($input, $tokens));
    }

    public function provideReplacesTokens()
    {
        yield 'empty' => [
            '',
            [],
            ''
        ];

        yield 'non-empty string' => [
            'nonempty',
            [],
            'nonempty'
        ];

        yield 'single token' => [
            '%token%',
            [
                'token' => 'single',
            ],
            'single'
        ];

        yield 'multiple same token' => [
            '%token% %token%',
            [
                'token' => 'single',
            ],
            'single single'
        ];

        yield 'multiple different token' => [
            '%token% %barfoo%',
            [
                'token' => 'single',
                'barfoo' => 'foobar',
            ],
            'single foobar'
        ];

        yield 'exception on undefined token' => [
            '%token%',
            [
                'bar' => 'foo',
            ],
            '',
            new UnknownToken('Token "token" not known, known tokens: "bar"')
        ];

        yield 'array token replaces value' => [
            '%token%',
            [
                'token' => ['single'],
            ],
            ['single']
        ];

        yield 'throws exception if unsupported value used as token value' => [
            '%token%',
            [
                'token' => new \stdClass(),
            ],
            '',
            new InvalidTokenType('Type "object" is not supported for tokens (in token "token")')
        ];
    }
}
