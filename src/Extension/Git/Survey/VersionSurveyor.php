<?php

namespace Maestro\Extension\Git\Survey;

use Amp\Promise;
use Maestro\Extension\Git\Model\Exception\GitException;
use Maestro\Extension\Git\Model\ExistingTag;
use Maestro\Extension\Git\Model\ExistingTags;
use Maestro\Extension\Git\Model\Git;
use Maestro\Extension\Git\Model\VersionReport;
use Maestro\Extension\Survey\Model\Surveyor;
use Maestro\Graph\Environment;
use Maestro\Graph\Exception\TaskFailed;
use Maestro\Package\Package;

class VersionSurveyor implements Surveyor
{
    /**
     * @var Git
     */
    private $git;

    public function __construct(Git $git)
    {
        $this->git = $git;
    }

    /**
     * {@inheritDoc}
     */
    public function survey(Environment $environment): Promise
    {
        $package = $environment->vars()->get('package');
        assert($package instanceof Package);

        return \Amp\call(function () use ($package, $environment) {
            $repoPath = $environment->workspace()->absolutePath();

            $path = $environment->workspace()->absolutePath();
            $env = $environment->env()->toArray();

            try {
                $tags = yield $this->git->listTags($repoPath);
                assert($tags instanceof ExistingTags);
                $mostRecentTag = $this->resolveMostRecentTag($tags);
            } catch (GitException $e) {
                throw new TaskFailed($e->getMessage());
            }
            $headId = yield $this->git->headId($repoPath);
            $headComment = yield $this->git->message($repoPath, $headId);

            $diff = yield $this->git->commitsBetween(
                $repoPath,
                $mostRecentTag ? $mostRecentTag->commitId() : $headId,
                $headId
            );

            return new VersionReport(
                $package->name(),
                $package->version(),
                $mostRecentTag ? $mostRecentTag->name() : null,
                $mostRecentTag ? $mostRecentTag->commitId() : null,
                $headId,
                $headComment,
                $diff
            );
        });
    }

    private function resolveMostRecentTag(ExistingTags $tags): ?ExistingTag
    {
        if ($tags->count()) {
            return $tags->mostRecent();
        }

        return null;
    }
}
