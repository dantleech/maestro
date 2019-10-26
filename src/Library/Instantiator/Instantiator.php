<?php

namespace Maestro\Library\Instantiator;

use Maestro\Library\Instantiator\Exception\ClassHasNoConstructor;
use Maestro\Library\Instantiator\Exception\InvalidParameterType;
use Maestro\Library\Instantiator\Exception\RequiredKeysMissing;
use Maestro\Library\Instantiator\Exception\UnknownKeys;
use ReflectionClass;
use ReflectionParameter;

class Instantiator
{
    public const MODE_TYPE = 1;
    public const MODE_NAME = 2;

    private const METHOD_CONSTRUCT = '__construct';

    /**
     * @var int
     */
    private $mode;

    public static function instantiate(string $className, array $data = [], $mode = self::MODE_NAME)
    {
        return (new self($mode))->doInstantiate($className, $data);
    }

    public static function call(object $object, string $methodName, array $args, int $mode = self::MODE_NAME)
    {
        return (new self($mode))->doCall($object, $methodName, $args);
    }

    public function __construct(int $mode)
    {
        $this->mode = $mode;
    }

    private function doCall(object $object, string $methodName, array $args)
    {
        $class = new ReflectionClass(get_class($object));
        $arguments = $this->resolveArguments($class, $methodName, $args);

        return $class->getMethod($methodName)->invoke($object, ...$arguments);
    }

    private function doInstantiate(string $className, array $args): object
    {
        $class = new ReflectionClass($className);

        if (!$class->hasMethod(self::METHOD_CONSTRUCT)) {
            if (empty($args)) {
                return $class->newInstance();
            }

            throw new ClassHasNoConstructor(sprintf(
                'Class "%s" has no constructor, but was instantiated with keys "%s"',
                $class->getName(),
                implode('", "', array_keys($args))
            ));
        }


        $arguments = $this->resolveArguments($class, self::METHOD_CONSTRUCT, $args);

        return $class->newInstanceArgs($arguments);
    }

    /**
     * @return ReflectionParameter[]
     */
    private function mapParameters(ReflectionClass $class, string $methodName): array
    {
        $parameters = [];
        foreach ($class->getMethod($methodName)->getParameters() as $reflectionParameter) {
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

    private function assertRequiredKeys(array $data, array $parameters, string $className)
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

            if ($reflectionType->isBuiltin() && $reflectionType->getName() === $typeName) {
                continue;
            }

            if (!$reflectionType->isBuiltin()) {
                $reflectionClass = new ReflectionClass($typeName);

                if ($typeName === $reflectionType->__toString() || $reflectionClass->isSubclassOf($reflectionType->__toString())) {
                    continue;
                }
            }

            throw new InvalidParameterType(sprintf(
                'Argument "%s" has type "%s" but was passed "%s" for "%s"',
                $parameter->getName(),
                $reflectionType->getName(),
                is_object($value) ? get_class($value) : gettype($value),
                $className
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

    private function resolveArguments(ReflectionClass $class, string $methodName, array $givenArgs): array
    {
        if ($this->mode === self::MODE_TYPE) {
            return $this->resolveArgumentsByType($class, $methodName, $givenArgs);
        }

        return $this->resolveArgumentsByName($class, $methodName, $givenArgs);
    }

    private function resolveArgumentsByName(ReflectionClass $class, string $methodName, array $givenArgs)
    {
        $parameters = $this->mapParameters($class, $methodName);
        
        $this->assertCorrectKeys($givenArgs, $parameters, $class->getName());
        $this->assertRequiredKeys($givenArgs, $parameters, $class->getName());
        $givenArgs = $this->mergeDefaults($parameters, $givenArgs);
        $this->assertTypes($givenArgs, $parameters, $class->getName());
        
        $arguments = [];
        foreach ($parameters as $name => $defaultValue) {
            $arguments[] = $givenArgs[$name];
        }
        return $arguments;
    }

    private function resolveArgumentsByType(ReflectionClass $class, string $methodName, array $givenArgs): array
    {
        $parameters = $this->mapParameters($class, $methodName);
        $resolved = [];

        foreach ($givenArgs as $givenArg) {
            foreach ($parameters as $parameter) {
                $type = $parameter->getType();

                if (null === $type) {
                    continue;
                }

                if (
                    gettype($givenArg) !== 'object' &&
                    $type->isBuiltin() &&
                    (string)$parameter->getType() === $this->resolveInternalTypeName($givenArg)
                ) {
                    $resolved[$parameter->getName()] = $givenArg;
                }

                if (gettype($givenArg) === 'object') {
                    if (is_a($givenArg, $type->__toString())) {
                        $resolved[$parameter->getName()] = $givenArg;
                    }
                }
            }
        }

        $this->assertRequiredKeys($resolved, $parameters, $class->getName());
        $resolved = $this->mergeDefaults($parameters, $resolved);

        return array_values($resolved);
    }
}
