<?php

namespace Maestro\Model\Package;

use Maestro\Model\Package\PackageDefinition;

final class PackageDefinitionBuilder
{
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public static function create(string $packageName): self
    {
        return new self($packageName);
    }

    public function build()
    {
        return new PackageDefinition($this->name);
    }
}
