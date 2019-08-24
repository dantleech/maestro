<?php

namespace Maestro\Extension\Git\Model;

use ArrayIterator;
use Countable;
use Iterator;
use IteratorAggregate;
use Maestro\Extension\Git\Model\Exception\GitException;

class ExistingTags implements IteratorAggregate, Countable
{
    /**
     * @var ExistingTag[]
     */
    private $tags = [];

    public function __construct(array $tags)
    {
        foreach ($tags as $element) {
            $this->add($element);
        }
        sort($this->tags);
    }

    private function add(ExistingTag $element): void
    {
        $this->tags[] = $element;
    }

    public function names(): array
    {
        return array_map(function (ExistingTag $tag) {
            return $tag->name();
        }, $this->tags);
    }

    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->tags);
    }

    public function mostRecent(): ExistingTag
    {
        if (count($this->tags) === 0) {
            throw new GitException(
                'Must have at least one tag in order to retrieve the most recent'
            );
        }

        return $this->tags[array_key_last($this->tags)];
    }

    public function has(string $tagName)
    {
        foreach ($this->tags as $tag) {
            if ($tag->name() === $tagName) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return count($this->tags);
    }
}
