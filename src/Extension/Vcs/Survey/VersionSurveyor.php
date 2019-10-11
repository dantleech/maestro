<?php
namespace Maestro\Extension\Vcs\Survey;

use Amp\Promise;
use Maestro\Library\Support\Package\Package;
use Maestro\Library\Survey\Surveyor;
use Maestro\Library\Task\Exception\TaskFailure;
use Maestro\Library\Vcs\Exception\VcsException;
use Maestro\Library\Vcs\RepositoryFactory;
use Maestro\Library\Vcs\Tag;
use Maestro\Library\Vcs\Tags;
use Maestro\Library\Workspace\Workspace;

class VersionSurveyor implements Surveyor
{
    /**
     * @var RepositoryFactory
     */
    private $repositoryFactory;

    public function __construct(RepositoryFactory $repositoryFactory)
    {
        $this->repositoryFactory = $repositoryFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(
        Package $package,
        Workspace $workspace
    ): Promise {
        return \Amp\call(function () use ($package, $workspace) {
            $repoPath = $workspace->absolutePath();
            $repository = $this->repositoryFactory->create($repoPath);

            try {
                $tags = yield $repository->listTags();
                assert($tags instanceof Tags);
                $mostRecentTag = $this->resolveMostRecentTag($tags);
            } catch (VcsException $e) {
                throw new TaskFailure($e->getMessage());
            }
            $headId = yield $repository->headId();
            $headComment = yield $repository->message($headId);

            $nbCommitsAhead = count(yield $repository->commitsBetween(
                $mostRecentTag ? $mostRecentTag->commitId() : $headId,
                $headId
            ));

            return new VersionResult(
                $package->name(),
                $package->version(),
                $mostRecentTag ? $mostRecentTag->name() : null,
                $mostRecentTag ? $mostRecentTag->commitId() : null,
                $headId,
                $headComment,
                $nbCommitsAhead
            );
        });
    }

    private function resolveMostRecentTag(Tags $tags): ?Tag
    {
        if ($tags->count()) {
            return $tags->mostRecent();
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function description(): string
    {
        return 'gathing version info from vcs repository';
    }
}
