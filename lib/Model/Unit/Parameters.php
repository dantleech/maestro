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
        return array_key_exists($key, $this->localParameters);
    }

    public function all(): array
    {
        return array_merge($this->globalParameters, $this->localParameters);
    }

    public function remove(string $string): Parameters
    {
        if (!isset($this->localParameters[$string])) {
            return $this;
        }

        $locals = $this->localParameters;
        unset($locals[$string]);

        return new self($locals, $this->globalParameters);
    }

    public function moveGlobalToLocal(string $key)
    {
        return new self(
            array_merge($this->localParameters, [$key => $this->getGlobal($key)]),
            $this->globalParameters
        );
    }

    public function hasGlobal(string $key): bool
    {
        return array_key_exists($key, $this->globalParameters);
    }

    public function getGlobal(string $key)
    {
        if (!isset($this->globalParameters[$key])) {
            throw new ParameterNotFound(sprintf(
                'Global parameter "%s" is not defined, defined parameters: "%s"',
                $key, implode('", "', array_keys($this->globalParameters))
            ));
        }

        return $this->globalParameters[$key];
    }
}
