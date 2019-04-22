<?php

namespace Maestro\Model\Package;

use Maestro\Model\Package\PackageDefinition;

final class PackageDefinitionBuilder
{
    private $name;
    private $initCommands = [];

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public static function create(string $packageName): self
    {
        return new self($packageName);
    }


    public function build(): PackageDefinition
    {
        return new PackageDefinition($this->name, $this->initCommands);
    }

    public function withInitCommands(array $initCommands): self
    {
        $this->initCommands = $initCommands;
        return $this;
    }
}
