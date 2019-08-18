<?php

namespace Maestro\Extension\Git\Task;

use Amp\Promise;
use Maestro\Extension\Git\Model\Exception\GitException;
use Maestro\Extension\Git\Model\ExistingTags;
use Maestro\Extension\Git\Model\Git;
use Maestro\Extension\Git\Model\VersionReport;
use Maestro\Graph\Environment;
use Maestro\Graph\Exception\TaskFailed;
use Maestro\Graph\Task;
use Maestro\Graph\TaskHandler;
use Maestro\Package\Package;

class VersionInfoHandler implements TaskHandler
{
    /**
     * @var Git
     */
    private $git;

    public function __construct(Git $git)
    {
        $this->git = $git;
    }

    public function execute(Task $task, Environment $environment): Promise
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
                $mostRecentTag = $tags->mostRecent();
            } catch (GitException $e) {
                throw new TaskFailed($e->getMessage());
            }
            $headId = yield $this->git->headId($repoPath);
            $headComment = yield $this->git->message($repoPath, $headId);

            $diff = yield $this->git->commitsBetween(
                $repoPath,
                $mostRecentTag->commitId(),
                $headId
            );

            return $environment->builder()
               ->withVars([
                   'versions' => new VersionReport(
                       $package->name(),
                       $package->version(),
                       $mostRecentTag->name(),
                       $mostRecentTag->commitId(),
                       $headId,
                       $headComment,
                       $diff
                   )
               ])->build();
        });
    }
}
