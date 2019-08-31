<?php

namespace Maestro\Extension\Maestro;

class TaskHandlerDefinition
{
    /**
     * @var string
     */
    private $serviceId;
    /**
     * @var string
     */
    private $alias;
    /**
     * @var string
     */
    private $taskClass;

    public function __construct(string $serviceId, string $alias, string $taskClass)
    {
        $this->serviceId = $serviceId;
        $this->alias = $alias;
        $this->taskClass = $taskClass;
    }

    public function alias(): string
    {
        return $this->alias;
    }

    public function serviceId(): string
    {
        return $this->serviceId;
    }

    public function taskClass(): string
    {
        return $this->taskClass;
    }
}
