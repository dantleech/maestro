<?php

namespace Maestro\Extension\Report\Extension;

class ConsoleReportDefinition
{
    /**
     * @var string
     */
    private $serviceId;

    /**
     * @var string
     */
    private $name;

    public function __construct(string $serviceId, string $name)
    {
        $this->serviceId = $serviceId;
        $this->name = $name;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function serviceId(): string
    {
        return $this->serviceId;
    }
}
