<?php

namespace Maestro\Model\Package;

use ArrayIterator;
use IteratorAggregate;
use RuntimeException;

class Manifest implements IteratorAggregate
{
    /**
     * @var ManifestItem[]
     */
    private $items;

    private function __construct(array $items)
    {
        $this->items = $items;
    }

    public static function fromArray(array $manifest)
    {
        $items = [];
        foreach ($manifest as $name => $item) {
            $item['name'] = $name;
            $items[$name] = Instantiator::create()->instantiate(ManifestItem::class, $item);
        }

        return new self($items);
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }

    public function get(string $string): ManifestItem
    {
        if (!isset($this->items[$string])) {
            throw new RuntimeException(sprintf(
                'Item "%s" not known, known items: "%s"',
                $string, implode('", "', array_keys($this->items))
            ));
        }

        return $this->items[$string];
    }
}
