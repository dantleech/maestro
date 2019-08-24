<?php

namespace Maestro\Extension\Survey\Model;

use ArrayIterator;
use IteratorAggregate;
use Maestro\Extension\Survey\Model\Exception\ResultNotRegistered;

final class Survey implements IteratorAggregate
{
    /**
     * @var array
     */
    private $results = [];

    public function __construct(array $results = [])
    {
        foreach ($results as $result) {
            $this->add($result);
        }
    }

    private function add(SurveyResult $result)
    {
        $this->results[get_class($result)] = $result;
    }

    public function get(string $resultFqn)
    {
        if (!isset($this->results[$resultFqn])) {
            throw new ResultNotRegistered(sprintf(
                'Result "%s" has not been registered, known results: "%s"',
                $resultFqn, implode('", "', array_keys($this->results))
            ));
        }
        return $this->results[$resultFqn];
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return new ArrayIterator($this->results);
    }
}
