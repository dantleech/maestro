<?php

namespace Maestro\Extension\Runner\Console;

use ReflectionClass;
use ReflectionParameter;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

final class MethodToInputDefinitionConverter
{
    public function inputDefinitionFor(
        string $className,
        string $methodName
    ): InputDefinition {
        $reflection = new ReflectionClass($className);
        $definition = new InputDefinition();
        
        if (!$reflection->hasMethod($methodName)) {
            return $definition;
        }

        $method = $reflection->getMethod($methodName);

        foreach ($method->getParameters() as $parameter) {
            $definition->addOption($this->createOption($parameter));
        }

        return $definition;
    }

    private function createArgument(ReflectionParameter $parameter)
    {
        return new InputArgument($parameter->getName(), InputArgument::REQUIRED);
    }

    private function createOption(ReflectionParameter $parameter)
    {
        $option = new InputOption(
            $parameter->getName(),
            null,
            $this->buildOptionMode($parameter),
            ''
        );

        $type = $parameter->getType();
        $type = $type ? $type->__toString() : null;
        if ($parameter->isDefaultValueAvailable() && $type !== 'bool') {
            $option->setDefault($parameter->getDefaultValue());
        }

        return $option;
    }

    private function buildOptionMode(ReflectionParameter $parameter)
    {
        $type = $parameter->getType();
        $type = $type ? $type->__toString() : null;
        if ($type === 'bool') {
            return InputOption::VALUE_NONE;
        }

        return InputOption::VALUE_REQUIRED;
    }
}
