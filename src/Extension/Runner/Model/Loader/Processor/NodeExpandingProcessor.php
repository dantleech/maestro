<?php

namespace Maestro\Extension\Runner\Model\Loader\Processor;

use Maestro\Extension\Runner\Model\Loader\Exception\PrototypeNotFound;
use Maestro\Extension\Runner\Model\Loader\Processor;

class NodeExpandingProcessor implements Processor
{
    const KEY_NODES = 'nodes';

    public function process(array $node): array
    {
        foreach ($node[self::KEY_NODES] ?? [] as $index => $childNode) {
            if (!isset($childNode['name'])) {
                continue;
            }

            $node[self::KEY_NODES][$childNode['name']] = $this->process($childNode);
            unset($node[self::KEY_NODES][$index]);
            unset($node[self::KEY_NODES][$childNode['name']]['name']);
        }


        return $node;
    }
}
