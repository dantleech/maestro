<?php

namespace Maestro\Model\Package;

use ArrayIterator;
use IteratorAggregate;
use Maestro\Model\Package\Exception\CircularReferenceDetected;
use Maestro\Model\Package\Exception\TargetNotFound;
use RuntimeException;
use Maestro\Model\Instantiator;

final class Manifest implements IteratorAggregate
{
    /**
     * @var ManifestItem[]
     */
    private $items;

    private $resolved = [];

    private function __construct(array $items)
    {
        foreach ($items as $item) {
            $this->add($item);
        }
    }

    public function forTarget(?string $target = null): self
    {
        if (null === $target) {
            return $this;
        }

        return Manifest::fromItems($this->resolveItems($target));
    }

    public static function fromItems(array $items): self
    {
        return new self($items);
    }

    public static function fromArray(array $manifest): self
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
        return new ArrayIterator(array_values($this->items));
    }

    public function get(string $string): ManifestItem
    {
        if (!isset($this->items[$string])) {
            throw new RuntimeException(sprintf(
                'Item "%s" not known, known items: "%s"',
                $string,
                implode('", "', array_keys($this->items))
            ));
        }

        return $this->items[$string];
    }

    private function add(ManifestItem $item): void
    {
        $this->items[$item->name()] = $item;
    }

    private function merge(Manifest $manifest): self
    {
        return new self(array_merge(
            $this->items,
            $manifest->items
        ));
    }

    private function resolveItems(string $target, $items = [], $seen = []): array
    {
        if (!isset($this->items[$target])) {
            throw new TargetNotFound(sprintf(
                'Target "%s" not found, known targets: "%s"',
                $target,
                implode('", "', array_keys($this->items))
            ));
        }

        $item = $this->items[$target];
        assert($item instanceof ManifestItem);
        $seen[$item->name()] = $item;
        $items[] = $item;

        foreach ($item->depends() as $dependency) {
            if (isset($seen[$dependency])) {
                throw new CircularReferenceDetected(sprintf(
                    'Circular reference detected: "%s" which depends on "%s"',
                    implode('" depends on "', array_keys($seen)),
                    $dependency
                ));
            }

            $items += $this->resolveItems($dependency, $items, $seen);
        }

        return $items;
    }
}
