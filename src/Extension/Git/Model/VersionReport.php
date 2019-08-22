<?php

namespace Maestro\Extension\Git\Model;

class VersionReport
{
    /**
     * @var string|null
     */
    private $configuredVersion;
    /**
     * @var string|null
     */
    private $taggedVersion;
    /**
     * @var string|null
     */
    private $taggedCommit;
    /**
     * @var string
     */
    private $headCommit;

    /**
     * @var string
     */
    private $packageName;

    /**
     * @var array
     */
    private $commitsBetween;

    /**
     * @var string
     */
    private $headMessage;

    public function __construct(
        string $packageName,
        ?string $configuredVersion,
        ?string $taggedVersion,
        ?string $taggedCommit,
        string $headCommit,
        string $headMessage,
        array $commitsBetween
    ) {
        $this->configuredVersion = $configuredVersion;
        $this->taggedVersion = $taggedVersion;
        $this->taggedCommit = $taggedCommit;
        $this->headCommit = $headCommit;
        $this->packageName = $packageName;
        $this->commitsBetween = $commitsBetween;
        $this->headMessage = $headMessage;
    }

    public function configuredVersion(): ?string
    {
        return $this->configuredVersion;
    }

    public function headCommit(): string
    {
        return $this->headCommit;
    }

    public function taggedCommit(): ?string
    {
        return $this->taggedCommit;
    }

    public function taggedVersion(): ?string
    {
        return $this->taggedVersion;
    }

    public function packageName(): string
    {
        return $this->packageName;
    }

    public function tagIsMostRecentCommit(): bool
    {
        return $this->taggedCommit() === $this->headCommit();
    }

    public function willBeTagged(): bool
    {
        return $this->configuredVersion !== null && $this->taggedVersion !== $this->configuredVersion;
    }

    public function commitsBetween(): array
    {
        return $this->commitsBetween;
    }

    public function divergence(): int
    {
        return count($this->commitsBetween);
    }

    public function headMessage(): string
    {
        return $this->headMessage;
    }
}
