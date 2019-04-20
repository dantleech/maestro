<?php

namespace Maestro\Model\Unit;

use Maestro\Model\Unit\Exception\InvalidDefinitionException;

final class Definition
{
    private const KEY_TYPE = 'unit';

    /**
     * @var string
     */
    private $type;

    /**
     * @var array
     */
    private $config;

    private function __construct(string $type, array $config)
    {
        $this->type = $type;
        $this->config = $config;
    }

    public static function fromArray(array $definition): self
    {
        if (!isset($definition[self::KEY_TYPE])) {
            throw new InvalidDefinitionException(sprintf(
                'Unit definitions must specify the "type" key, got keys: "%s"',
                implode('", "', array_keys($definition))
            ));
        }

        $type = $definition[self::KEY_TYPE];
        unset($definition[self::KEY_TYPE]);

        return new self($type, $definition);
    }

    public function config(): array
    {
        return $this->config;
    }

    public function type(): string
    {
        return $this->type;
    }
}
