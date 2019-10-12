<?php

namespace Maestro\Extension\Vcs\Survey;

use Maestro\Library\Artifact\Artifact;

class VersionResult implements Artifact
{
    /**
     * @var string
     */
    public $packageName;

    /**
     * @var string|null
     */
    public $configuredVersion;

    /**
     * @var string|null
     */
    public $mostRecentTagName;

    /**
     * @var string|null
     */
    public $mostRecentTagCommitId;

    /**
     * @var string|null
     */
    public $headId;

    /**
     * @var string|null
     */
    public $headComment;

    /**
     * @var int|null
     */
    public $nbCommitsAhead;

    public function __construct(
        string $packageName,
        ?string $configuredVersion,
        ?string $mostRecentTagName,
        ?string $mostRecentTagCommitId,
        ?string $headId,
        ?string $headComment,
        ?int $nbCommitsAhead
    ) {
        $this->packageName = $packageName;
        $this->configuredVersion = $configuredVersion;
        $this->mostRecentTagName = $mostRecentTagName;
        $this->mostRecentTagCommitId = $mostRecentTagCommitId;
        $this->headId = $headId;
        $this->headComment = $headComment;
        $this->nbCommitsAhead = $nbCommitsAhead;
    }

    public function nbCommitsAhead(): ?int
    {
        return $this->nbCommitsAhead;
    }

    public function headComment(): ?string
    {
        return $this->headComment;
    }

    public function headId(): ?string
    {
        return $this->headId;
    }

    public function mostRecentTagCommitId(): ?string
    {
        return $this->mostRecentTagCommitId;
    }

    public function mostRecentTagName(): ?string
    {
        return $this->mostRecentTagName;
    }

    public function packageName(): string
    {
        return $this->packageName;
    }

    public function configuredVersion(): ?string
    {
        return $this->configuredVersion;
    }

    public function tagIsMostRecentCommit(): bool
    {
        return $this->mostRecentTagCommitId() === $this->headId();
    }

    public function willBeTagged(): bool
    {
        return $this->configuredVersion !== null && $this->mostRecentTagName !== $this->configuredVersion;
    }
}
