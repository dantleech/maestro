<?php

namespace Maestro\Tests\Unit\Model\Package;

use Maestro\Model\Instantiator;
use Maestro\Model\Package\Exception\RequiredKeysMissing;
use Maestro\Model\Package\Exception\UnknownKeys;
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

    public function testIfOptionalDataIsProvidedThenItIsUsed()
    {
        $this->assertEquals(
            new TestClass3('foobar', 'zedzed'),
            Instantiator::create()->instantiate(TestClass3::class, [
                'one' => 'foobar',
            ], [
                'two' => 'zedzed'
            ])
        );
    }

    public function testDoesNotThrowExceptionIfAdditionalOptionalParametersArePassed()
    {
        $this->assertEquals(
            new TestClass3('foobar', 'barfoo'),
            Instantiator::create()->instantiate(TestClass3::class, [
                'one' => 'foobar',
            ], [
                'three' => 'zedzed'
            ])
        );
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
