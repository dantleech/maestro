<?php

namespace Maestro\Extension\Runner\Model\Loader\Processor;

use Maestro\Extension\Runner\Model\Loader\Exception\PrototypeNotFound;
use Maestro\Extension\Runner\Model\Loader\Processor;

class NameNormalizingProcessor implements Processor
{
    /**
     * @var string
     */
    private $type;

    private function __construct(string $type)
    {
        $this->type = $type;
    }

    public static function forNodes()
    {
        return new self('nodes');
    }

    public static function forPrototypes()
    {
        return new self('prototypes');
    }

    public function process(array $node): array
    {
        foreach ($node[$this->type] ?? [] as $index => $childNode) {
            if (!isset($childNode['name'])) {
                continue;
            }

            $node[$this->type][$childNode['name']] = $this->process($childNode);
            unset($node[$this->type][$index]);
            unset($node[$this->type][$childNode['name']]['name']);
        }


        return $node;
    }
}
