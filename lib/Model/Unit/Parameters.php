<?php

namespace Maestro\Model\Unit;

use Maestro\Model\Unit\Exception\ParameterNotFound;

final class Parameters
{
    /**
     * @var array
     */
    private $localParameters;

    /**
     * @var array
     */
    private $globalParameters;

    private function __construct(array $localParameters, array $globalParameters)
    {
        $this->localParameters = $localParameters;
        $this->globalParameters = $globalParameters;
    }

    public static function create(array $localParameters, array $globalParameters = []): self
    {
        return new self($localParameters, $globalParameters);
    }

    public function spawnLocal($unitParameters): self
    {
        return new self($unitParameters, array_merge(
            $this->localParameters,
            $this->globalParameters
        ));
    }

    public function get(string $key)
    {
        if (!isset($this->localParameters[$key])) {
            throw new ParameterNotFound(sprintf(
                'Local parameter "%s" is not defined, defined parameters: "%s"',
                $key, implode('", "', array_keys($this->localParameters))
            ));
        }

        return $this->localParameters[$key];
    }

    public function has(string $key): bool
    {
        return isset($this->localParameters[$key]);
    }

    public function all(): array
    {
        return array_merge($this->globalParameters, $this->localParameters);
    }
}
