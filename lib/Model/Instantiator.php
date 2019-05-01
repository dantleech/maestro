<?php

namespace Maestro\Model;

use Maestro\Model\Package\Exception\RequiredKeysMissing;
use Maestro\Model\Package\Exception\UnknownKeys;
use ReflectionClass;
use ReflectionParameter;

class Instantiator
{
    public static function create(): self
    {
        return new self();
    }

    public function instantiate(string $className, array $data, array $optionalData = [])
    {
        $class = new ReflectionClass($className);

        if (!$class->hasMethod('__construct')) {
            return $class->newInstance();
        }

        $parameters = $this->mapParameters($class);
        $this->assertCorrectKeys($data, $parameters, $className);
        $data = array_merge($data, $optionalData);
        $this->assertRequiredKeys($data, $parameters, $className);
        $data = $this->mergeDefaults($parameters, $data);

        $arguments = [];
        foreach ($parameters as $name => $defaultValue) {
            $arguments[] = $data[$name];
        }

        return $class->newInstanceArgs($arguments);
    }

    private function mapParameters(ReflectionClass $class)
    {
        $parameters = [];
        foreach ($class->getMethod('__construct')->getParameters() as $reflectionParameter) {
            $parameters[$reflectionParameter->getName()] = $reflectionParameter;
        }
        
        return $parameters;
    }

    private function assertCorrectKeys(array $data, $parameters, string $className)
    {
        if (!$diff = array_diff(array_keys($data), array_keys($parameters))) {
            return;
        }

        throw new UnknownKeys(sprintf(
            'Unknown keys "%s" for "%s", known keys: "%s"',
            implode('", "', $diff),
            $className,
            implode('", "', array_keys($parameters))
        ));
    }

    private function assertRequiredKeys(array $data, $parameters, string $className)
    {
        $requiredParameters = array_filter($parameters, function (ReflectionParameter $parameter) {
            return (bool) !$parameter->isDefaultValueAvailable();
        });

        if (!$diff = array_diff(array_keys($requiredParameters), array_keys($data))) {
            return;
        }

        throw new RequiredKeysMissing(sprintf(
            'Required keys "%s" for "%s", are missing',
            implode('", "', $diff),
            $className
        ));
    }

    private function mergeDefaults($parameters, array $data): array
    {
        $defaults = array_map(function (ReflectionParameter $parameter) {
            return $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null;
        }, $parameters);
        
        $data = array_merge($defaults, $data);
        return $data;
    }
}
