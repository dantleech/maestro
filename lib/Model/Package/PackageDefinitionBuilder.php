<?php

namespace Maestro\Model\Package;

use Maestro\Model\Package\Exception\InvalidPackageDefinition;

final class PackageDefinitionBuilder
{
    const KEY_INITIALIZE = 'initialize';
    const KEY_URL = 'url';
    const KEY_MANIFEST = 'manifest';

    private $name;
    private $initialize = [];
    private $url;

    /**
     * @var array
     */
    private $manifest = [];

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public static function create(string $packageName): self
    {
        return new self($packageName);
    }

    public static function createFromArray(string $packageName, array $data): self
    {
        $packageBuilder = self::create($packageName);
        $data = self::validateDefinition($data);

        if ($data[self::KEY_MANIFEST]) {
            $packageBuilder = $packageBuilder->withManifest($data[self::KEY_MANIFEST]);
        }

        return $packageBuilder;
    }


    public function build(): PackageDefinition
    {
        return new PackageDefinition($this->name, $this->initialize, $this->manifest);
    }

    public function withInitCommands(array $initialize): self
    {
        $this->initialize = $initialize;
        return $this;
    }

    public function withManifest(array $manifest): self
    {
        $this->manifest = $manifest;
        return $this;
    }

    private static function validateDefinition(array $definition): array
    {
        $defaults = [
            self::KEY_INITIALIZE => [],
            self::KEY_URL => null,
            self::KEY_MANIFEST => [],
        ];

        if ($diff = array_diff(array_keys($definition), array_keys($defaults))) {
            throw new InvalidPackageDefinition(sprintf(
                'Unexpected keys "%s", allowed keys: "%s"',
                implode('", "', $diff),
                implode('", "', array_keys($defaults))
            ));
        }

        return array_merge($defaults, $definition);
    }

    private function buildUrl(): string
    {
        if ($this->url) {
            return $this->url;
        }

        return 'git@github.com:' . $this->name;
    }
}
