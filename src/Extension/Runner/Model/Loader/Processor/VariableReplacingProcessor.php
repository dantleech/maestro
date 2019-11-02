<?php

namespace Maestro\Extension\Runner\Model\Loader\Processor;

use Maestro\Extension\Runner\Model\Loader\Processor;

class VariableReplacingProcessor implements Processor
{
    const DELIMITER = '%';

    public function process(array $data): array
    {
        return $this->doProcess($data);
    }

    private function doProcess(array $node, array $vars = []): array
    {
        if (isset($node['vars'])) {
            $vars = array_merge($vars, $node['vars']);
        }

        foreach ($node['args'] ?? [] as $argName => $value) {
            $node['args'][$argName] = $this->replaceVars($value, $vars);
        }

        foreach ($node['nodes'] ?? [] as $childName => $childNode) {
            $node['nodes'][$childName] = $this->doProcess($childNode, $vars);
        }

        return $node;
    }

    private function replaceVars($value, array $vars)
    {
        if (is_array($value)) {
            foreach ($value as $nestedKey => &$nestedValue) {
                $nestedValue = $this->replaceVars($nestedValue, $vars);
            }
            return $value;
        }

        foreach ($vars as $key => $varValue) {
            $value = str_replace(self::DELIMITER.$key.self::DELIMITER, $varValue, $value);
        }
        return $value;
    }
}
