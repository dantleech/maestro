<?php

namespace Maestro\Loader;

use Maestro\Loader\Exception\ClassHasNoConstructor;
use Maestro\Loader\Exception\InvalidParameterType;
use Maestro\Loader\Exception\RequiredKeysMissing;
use Maestro\Loader\Exception\UnknownKeys;
use ReflectionClass;
use ReflectionParameter;

class Instantiator
{
    public static function create(): self
    {
        return new self();
    }

    public function instantiate(string $className, array $data)
    {
        $class = new ReflectionClass($className);

        if (!$class->hasMethod('__construct')) {
            if (empty($data)) {
                return $class->newInstance();
            }

            throw new ClassHasNoConstructor(sprintf(
                'Class "%s" has no constructor, but was instantiated with keys "%s"',
                $className,
                implode('", "', array_keys($data))
            ));
        }

        $parameters = $this->mapParameters($class);
        $this->assertCorrectKeys($data, $parameters, $className);
        $this->assertRequiredKeys($data, $parameters, $className);
        $data = $this->mergeDefaults($parameters, $data);
        $this->assertTypes($data, $parameters, $className);

        $arguments = [];
        foreach ($parameters as $name => $defaultValue) {
            $arguments[] = $data[$name];
        }

        return $class->newInstanceArgs($arguments);
    }

    private function mapParameters(ReflectionClass $class): array
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

    private function assertTypes(array $data, array $parameters, string $className)
    {
        foreach ($data as $key => $value) {
            if (!isset($parameters[$key])) {
                continue;
            }

            $parameter = $parameters[$key];

            assert($parameter instanceof ReflectionParameter);

            $reflectionType = $parameter->getType();

            if (!$reflectionType) {
                continue;
            }

            if ($reflectionType->allowsNull() && is_null($value)) {
                continue;
            }

            $typeName = is_object($value) ? get_class($value) : gettype($value);

            if (!is_object($value)) {
                $typeName = $this->resolveInternalTypeName($value);
            }

            if ($reflectionType->getName() === $typeName) {
                continue;
            }

            throw new InvalidParameterType(sprintf(
                'Argument "%s" has type "%s" but was passed "%s"',
                $parameter->getName(),
                $reflectionType->getName(),
                gettype($value)
            ));
        }
    }

    private function resolveInternalTypeName($value): string
    {
        $type = gettype($value);

        if ($type === 'integer') {
            return 'int';
        }

        if ($type === 'boolean') {
            return 'bool';
        }

        return $type;
    }
}
