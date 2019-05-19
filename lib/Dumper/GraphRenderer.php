<?php

namespace Maestro\Dumper;

use Maestro\Task\Node;

class GraphRenderer
{
    public function render(Node $node, $depth = 0)
    {
        $out ='';

        if ($depth === 0) {
            $out .= "\033[2J";
            $out .= "\033[H";
        }

        $out .= sprintf(
            '%s[%s] %s (%s)',
            str_repeat('  ', $depth),
            "\033[34m" . $node->name() . "\033[0m",
            $node->task()->description(),
            "\033[32m" . $node->state()->toString() . "\033[0m"
        ) . PHP_EOL;

        foreach ($node->children() as $child) {
            $out .= $this->render($child, $depth + 1);
        }

        return $out;
    }
}
