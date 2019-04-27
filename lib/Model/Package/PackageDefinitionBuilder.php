<?php

namespace Maestro\Model\Package;

use Maestro\Model\Package\Exception\InvalidPackageDefinition;

final class PackageDefinitionBuilder
{
    const KEY_INITIALIZE = 'initialize';
    const KEY_URL = 'url';
    const KEY_FILES = 'files';

    private $name;
    private $initCommands = [];
    private $url;

    /**
     * @var array
     */
    private $files = [];

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

        if ($data[self::KEY_INITIALIZE]) {
            $packageBuilder = $packageBuilder->withInitCommands($data[self::KEY_INITIALIZE]);
        }

        if ($data[self::KEY_URL]) {
            $packageBuilder = $packageBuilder->withUrl($data[self::KEY_URL]);
        }

        if ($data[self::KEY_FILES]) {
            $packageBuilder = $packageBuilder->withFiles($data[self::KEY_FILES]);
        }

        return $packageBuilder;
    }


    public function build(): PackageDefinition
    {
        return new PackageDefinition($this->name, $this->initCommands, $this->buildUrl(), $this->files);
    }

    public function withInitCommands(array $initCommands): self
    {
        $this->initCommands = $initCommands;
        return $this;
    }

    public function withUrl(string $url): self
    {
        $this->url = $url;
        return $this;
    }

    public function withFiles(array $files): self
    {
        $this->files = $files;
        return $this;
    }

    private static function validateDefinition(array $definition): array
    {
        $defaults = [
            self::KEY_INITIALIZE => [],
            self::KEY_URL => null,
            self::KEY_FILES => [],
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
