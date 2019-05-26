<?php

namespace Maestro\Dumper;

use Maestro\Task\Node;

class GraphRenderer
{
    private $clear;

    public function __construct($clear = false)
    {
        $this->clear = $clear;
    }

    public function render(Node $node, $depth = 0)
    {
        $visibility = $this->walkVisibility($node);
        return $this->walkNode($node, $depth, $visibility);
    }

    private function walkNode(Node $node, int $depth, array $visibility)
    {
        $out ='';

        if (isset($visibility[spl_object_hash($node)])) {
            return $out;
        }
        
        if ($this->clear && $depth === 0) {
            $out .= "\033[2J";
            $out .= "\033[H";
        }
        
        $out .= sprintf(
            '%s[%s] %s (%s) %s',
            str_repeat('  ', $depth),
            "\033[34m" . $node->name() . "\033[0m",
            $node->task()->description(),
            $node->state()->isIdle() ? '' : $node->state()->isFailed() ? "\033[31m" . $node->state()->toString() . "\033[0m" : "\033[32m" . $node->state()->toString() . "\033[0m",
            " " . json_encode($node->artifacts()->toArray()),
            ) . PHP_EOL;
        
        foreach ($node->children() as $child) {
            $out .= $this->walkNode($child, $depth + 1, $visibility);
        }
        
        return $out;
    }

    private function walkVisibility(Node $node): array
    {
        $visibility = [];
        if ($node->children()->count() === 0) {
            foreach ($node->selfAndAncestors() as $ancestor) {
                if (!$ancestor->state()->isWaiting()) {
                    break;
                }

                $visibility[spl_object_hash($ancestor)] = false;
            }
        }
        foreach ($node->children() as $child) {
            $visibility = array_merge($visibility, $this->walkVisibility($child));
        }

        return $visibility;
    }
}
