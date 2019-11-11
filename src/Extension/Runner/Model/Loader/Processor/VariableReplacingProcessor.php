<?php

namespace Maestro\Extension\Runner\Model\Loader\Processor;

use Maestro\Extension\Runner\Model\Loader\Processor;
use Maestro\Library\TokenReplacer\TokenReplacer;

class VariableReplacingProcessor implements Processor
{
    /**
     * @var TokenReplacer
     */
    private $tokenReplacer;

    public function __construct(TokenReplacer $tokenReplacer)
    {
        $this->tokenReplacer = $tokenReplacer;
    }

    public function process(array $data): array
    {
        return $this->doProcess($data);
    }

    private function doProcess(array $node, array $vars = [], string $nodeName = null): array
    {
        if (null !== $nodeName) {
            $vars['_name'] = $nodeName;
        }

        if (isset($node['vars'])) {
            $vars = array_merge($vars, $node['vars']);
        }

        foreach ($node['args'] ?? [] as $argName => $value) {
            $node['args'][$argName] = $this->replaceVars($value, $vars);
        }

        foreach ($node['nodes'] ?? [] as $childName => $childNode) {
            $node['nodes'][$childName] = $this->doProcess($childNode, $vars, $childName);
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

        if (!is_string($value)) {
            return $value;
        }

        return $this->tokenReplacer->replace($value, $vars);
    }
}
