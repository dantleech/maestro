<?php

namespace Maestro\Model\Job;

use Maestro\Model\Instantiator;
use Maestro\Model\Job\Exception\JobNotFound;

class JobFactory
{
    /**
     * @var array
     */
    private $jobClassMap;

    public function __construct(array $jobClassMap)
    {
        $this->jobClassMap = $jobClassMap;
    }

    public function create(string $type, array $parameters, array $optionalParameters): Job
    {
        if (!isset($this->jobClassMap[$type])) {
            throw new JobNotFound(sprintf(
                'No job registered for type "%s", known jobs: "%s"',
                $type, implode('", "', array_keys($this->jobClassMap))
            ));
        }

        return Instantiator::create()->instantiate(
            $this->jobClassMap[$type],
            $parameters,
            $optionalParameters
        );
    }
}
