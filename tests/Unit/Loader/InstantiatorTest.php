<?php

namespace Maestro\Tests\Unit\Loader;

use Maestro\Loader\Instantiator;
use Maestro\Loader\Exception\InvalidParameterType;
use Maestro\Loader\Exception\RequiredKeysMissing;
use Maestro\Loader\Exception\UnknownKeys;
use PHPUnit\Framework\TestCase;

class InstantiatorTest extends TestCase
{
    public function testWithNoConstructor()
    {
        $this->assertEquals(new TestClass1(), Instantiator::create()->instantiate(TestClass1::class, []));
    }

    public function testWithConstructorWithArgument()
    {
        $this->assertEquals(
            new TestClass2('foobar'),
            Instantiator::create()->instantiate(TestClass2::class, [
                'one' => 'foobar'
            ])
        );
    }

    public function testExceptionIfKeyIsNotSet()
    {
        $this->expectException(UnknownKeys::class);
        $this->assertEquals(
            new TestClass2('foobar'),
            Instantiator::create()->instantiate(TestClass2::class, [
                'two' => 'foobar'
            ])
        );
    }

    public function testExceptionIfRequiredPropertyMissing()
    {
        $this->expectException(RequiredKeysMissing::class);
        $this->assertEquals(
            new TestClass2('foobar'),
            Instantiator::create()->instantiate(TestClass2::class, [])
        );
    }

    public function testUsesDefaultValues()
    {
        $this->assertEquals(
            new TestClass3('foobar', 'barfoo'),
            Instantiator::create()->instantiate(TestClass3::class, [
                'one' => 'foobar',
            ])
        );
    }

    /**
     * @dataProvider provideValidatesTypes
     */
    public function testValidatesTypes(array $params, string $expectedExceptionMessage = null)
    {
        if ($expectedExceptionMessage) {
            $this->expectException(InvalidParameterType::class);
            $this->expectExceptionMessageRegExp('/' . $expectedExceptionMessage . '/');
        }

        $object = Instantiator::create()->instantiate(TestClass4::class, $params);
        $this->assertInstanceOf(TestClass4::class, $object);
    }

    public function provideValidatesTypes()
    {
        yield 'no params' => [
            [],
            null
        ];

        yield 'string for array' => [
            [
                'array' => 'foobar',
            ],
            'Argument "array" has type "array" but was passed "string"'
        ];

        yield 'subclass of declared class' => [
            [
                'subclass' => new SubClassOfTestClass1(),
            ],
        ];

        yield 'declared class' => [
            [
                'subclass' => new TestClass1(),
            ],
        ];
    }
}

class TestClass1
{
}

class TestClass2
{
    /**
     * @var string
     */
    private $one;

    public function __construct(string $one)
    {
        $this->one = $one;
    }
}

class TestClass3
{
    /**
     * @var string
     */
    private $one;
    /**
     * @var string
     */
    private $two;

    public function __construct(string $one, string $two = 'barfoo')
    {
        $this->one = $one;
        $this->two = $two;
    }
}

class TestClass4
{
    /**
     * @var string
     */
    private $string;
    /**
     * @var array
     */
    private $array;
    /**
     * @var int
     */
    private $int;
    /**
     * @var bool
     */
    private $bool;

    public function __construct(string $string = '', array $array = [], int $int = 1, bool $bool = false, TestClass1 $subclass = null)
    {
        $this->string = $string;
        $this->array = $array;
        $this->int = $int;
        $this->bool = $bool;
    }
}

class SubClassOfTestClass1 extends TestClass1
{
}
