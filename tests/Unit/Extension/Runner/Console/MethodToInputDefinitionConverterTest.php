<?php

namespace Maestro\Tests\Unit\Extension\Runner\Console;

use Maestro\Extension\Runner\Console\MethodToInputDefinitionConverter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputDefinition;

class MethodToInputDefinitionConverterTest extends TestCase
{
    /**
     * @var MethodToInputDefinitionConverter
     */
    private $converter;

    protected function setUp(): void
    {
        $this->converter = new MethodToInputDefinitionConverter();
    }

    public function testEmptyDefinitionIfMethodDoesNotExist()
    {
        $definition = $this->converter->inputDefinitionFor(TestSubject::class, 'idonotexist__');
        $this->assertInstanceOf(InputDefinition::class, $definition);
    }

    public function testNoParameters()
    {
        $definition = $this->converter->inputDefinitionFor(TestSubject::class, 'noParameters');
        $this->assertInstanceOf(InputDefinition::class, $definition);
    }

    public function testScalarOption()
    {
        $definition = $this->converter->inputDefinitionFor(TestSubject::class, 'scalarSingleOption');
        $this->assertInstanceOf(InputDefinition::class, $definition);
        $this->assertCount(1, $definition->getOptions());
        $this->assertEquals('foo', $definition->getOption('foo')->getName());
        $this->assertTrue($definition->getOption('foo')->isValueRequired());
    }

    public function testBooleanOption()
    {
        $definition = $this->converter->inputDefinitionFor(TestSubject::class, 'booleanSingleOption');
        $this->assertInstanceOf(InputDefinition::class, $definition);
        $this->assertCount(1, $definition->getOptions());
        $this->assertEquals('foo', $definition->getOption('foo')->getName());
        $this->assertFalse($definition->getOption('foo')->isValueRequired());
    }
}

class TestSubject
{
    public function noParameters()
    {
    }

    public function scalarSingleArg(string $foo)
    {
    }

    public function scalarSingleOption(string $foo = 'foo')
    {
    }

    public function booleanSingleOption(bool $foo = false)
    {
    }
}
