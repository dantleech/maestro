<?php

namespace Maestro\Library\Survey;

use ArrayIterator;
use Iterator;
use IteratorAggregate;

class Surveyors implements IteratorAggregate
{
    private $surveyors = [];

    public function __construct(array $surveyors)
    {
        foreach ($surveyors as $surveyor) {
            $this->add($surveyor);
        }
    }

    private function add(Surveyor $surveyor): void
    {
        $this->surveyors[] = $surveyor;
    }

    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->surveyors);
    }
}
