<?php

namespace Maestro\Model\Unit\Config;

use Maestro\Model\Unit\Parameters;
use Maestro\Model\Unit\Resolver;
use Maestro\Model\Unit\Unit;

class ParameterReplacementResolver implements Resolver
{
    /**
     * @var Resolver
     */
    private $innerResolver;

    public function __construct(Resolver $innerResolver)
    {
        $this->innerResolver = $innerResolver;
    }

    public function resolveConfig(Unit $unit, array $rawConfig, Parameters $parameters): array
    {
        $replacements = [];
        foreach ($parameters->all() as $key => $value) {
            $replacements['%' . $key . '%'] = $value;
        }

        foreach ($rawConfig  as $key => &$value) {
            $value = str_replace(array_keys($replacements), array_values($replacements), $value);
        }

        $resolved = $this->innerResolver->resolveConfig($unit, $rawConfig, $parameters);

        return $resolved;
    }
}
