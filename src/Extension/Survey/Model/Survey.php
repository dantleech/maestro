<?php

namespace Maestro\Extension\Survey\Model;

use ArrayIterator;
use IteratorAggregate;

final class Survey implements IteratorAggregate
{
    /**
     * @var array
     */
    private $results;

    public function __construct(array $results)
    {
        foreach ($results as $result) {
            $this->add($result);
        }
    }

    private function add(SurveyResult $result)
    {
        $this->results[] = $result;
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return new ArrayIterator($this->results);
    }
}
