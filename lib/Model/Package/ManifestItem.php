<?php

namespace Maestro\Model\Package;

class ManifestItem
{
    /**
     * @var string
     */
    private $source;

    /**
     * @var string
     */
    private $dest;

    /**
     * @var string
     */
    private $name;

    public function __construct(string $name, string $source = null, string $dest = null)
    {
        $this->source = $source ?: $name;
        $this->dest = $dest ?: $name;
        $this->name = $name;
    }

    public function dest(): string
    {
        return $this->dest;
    }

    public function source(): string
    {
        return $this->source;
    }

    public function name(): string
    {
        return $this->name;
    }
}
