<?php

namespace Maestro\Extension\Serializer\Extension;

final class NormalizerDefinition
{
    /**
     * @var string
     */
    private $serviceId;

    /**
     * @var int
     */
    private $priority;

    public function __construct(string $serviceId, int $priority = 0)
    {
        $this->serviceId = $serviceId;
        $this->priority = $priority;
    }

    public function priority(): int
    {
        return $this->priority;
    }

    public function serviceId(): string
    {
        return $this->serviceId;
    }
}
