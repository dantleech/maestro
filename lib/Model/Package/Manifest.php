<?php

namespace Maestro\Model\Package;

use ArrayIterator;
use IteratorAggregate;

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

    public function fromArray(array $manifest)
    {
        $items = [];
        foreach ($manifest as $name => $item) {
            $item['name'] = $name;
            $items[] = Instantiator::create()->instantiate(ManifestItem::class, $item);
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
}
