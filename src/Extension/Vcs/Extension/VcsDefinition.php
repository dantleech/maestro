<?php

namespace Maestro\Extension\Vcs\Extension;

class VcsDefinition
{
    /**
     * @var string
     */
    private $serviceId;
    /**
     * @var string
     */
    private $type;

    public function __construct(string $serviceId, string $type)
    {
        $this->serviceId = $serviceId;
        $this->type = $type;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function serviceId(): string
    {
        return $this->serviceId;
    }
}
