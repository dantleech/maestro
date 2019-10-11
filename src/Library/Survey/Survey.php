<?php

namespace Maestro\Library\Survey;

use ArrayIterator;
use IteratorAggregate;
use Maestro\Library\Survey\Exception\ResultNotRegistered;
use Maestro\Library\Artifact\Artifact;
use RuntimeException;

final class Survey implements IteratorAggregate, Artifact
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

    private function add(Artifact $result)
    {
        if (!is_object($result)) {
            throw new RuntimeException(sprintf(
                'Survey results must be objects, got "%s"',
                gettype($result)
            ));
        }

        $this->results[get_class($result)] = $result;
    }

    public function get(string $resultFqn, $default = null)
    {
        if (!isset($this->results[$resultFqn])) {
            if ($default) {
                return $default;
            }

            throw new ResultNotRegistered(sprintf(
                'Result "%s" has not been registered, known results: "%s"',
                $resultFqn,
                implode('", "', array_keys($this->results))
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

    /**
     * @return SurveyResult[]
     */
    public function toArray(): array
    {
        return $this->results;
    }
}
