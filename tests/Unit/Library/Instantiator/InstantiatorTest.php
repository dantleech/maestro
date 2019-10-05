<?php

namespace Maestro\Tests\Unit\Library\Instantiator;

use Maestro\Library\Instantiator\Instantiator;
use Maestro\Library\Instantiator\Exception\InvalidParameterType;
use Maestro\Library\Instantiator\Exception\RequiredKeysMissing;
use Maestro\Library\Instantiator\Exception\UnknownKeys;
use PHPUnit\Framework\TestCase;

class InstantiatorTest extends TestCase
{
    public function testWithNoConstructor()
    {
        $this->assertEquals(new TestClass1(), Instantiator::instantiate(TestClass1::class, []));
    }

    public function testWithConstructorWithArgument()
    {
        $this->assertEquals(
            new TestClass2('foobar'),
            Instantiator::instantiate(TestClass2::class, [
                'one' => 'foobar'
            ])
        );
    }

    public function testExceptionIfKeyIsNotSet()
    {
        $this->expectException(UnknownKeys::class);
        $this->assertEquals(
            new TestClass2('foobar'),
            Instantiator::instantiate(TestClass2::class, [
                'two' => 'foobar'
            ])
        );
    }

    public function testExceptionIfRequiredPropertyMissing()
    {
        $this->expectException(RequiredKeysMissing::class);
        $this->assertEquals(
            new TestClass2('foobar'),
            Instantiator::instantiate(TestClass2::class, [])
        );
    }

    public function testUsesDefaultValues()
    {
        $this->assertEquals(
            new TestClass3('foobar', 'barfoo'),
            Instantiator::instantiate(TestClass3::class, [
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

        $object = Instantiator::instantiate(TestClass4::class, $params);
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

    public function testInvokeWithNamedParameters()
    {
        $subject = new TestClass5();
        Instantiator::call($subject, 'callMe', [
            'string' => 'hello',
        ]);
        $this->assertEquals('hello', $subject->string);
    }

    public function testInvokeInvokeWithTypesOnly()
    {
        $subject = new TestClass6();
        Instantiator::call($subject, 'callMe', [
            true,
            'hello',
            ['goodbye'],
            12,
            new TestClass1(),
        ], Instantiator::MODE_TYPE);

        $this->assertEquals(true, $subject->bool);
        $this->assertEquals('hello', $subject->string);
        $this->assertEquals(['goodbye'], $subject->array);
        $this->assertEquals(12, $subject->int);
        $this->assertInstanceOf(TestClass1::class, $subject->class);
    }

    public function testInvokeExceptionOnMissingTypes()
    {
        $this->expectException(RequiredKeysMissing::class);
        $subject = new TestClass6();
        Instantiator::call($subject, 'callMe', [
            'hello',
        ], Instantiator::MODE_TYPE);

        $this->assertEquals(true, $subject->bool);
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

class TestClass5
{
    /**
     * @var string
     */
    public $string;

    public function callMe(string $string = '')
    {
        $this->string = $string;
    }
}

class TestClass6
{
    /**
     * @var string
     */
    public $string;
    /**
     * @var array
     */
    public $array;
    /**
     * @var int
     */
    public $int;
    /**
     * @var bool
     */
    public $bool;

    public $class;

    public function callMe(
        string $string,
        array $array,
        int $int,
        bool $bool,
        TestClass1 $class
    ) {
        $this->string = $string;
        $this->array = $array;
        $this->int = $int;
        $this->bool = $bool;
        $this->class = $class;
    }
}

class SubClassOfTestClass1 extends TestClass1
{
}
