<?php

declare(strict_types=1);

namespace Maestro\Extension\AssertFileExists\Job;

use Amp\Promise;
use Amp\Success;
use Maestro\Model\Job\Exception\JobFailure;
use Maestro\Model\Package\Workspace;
use Maestro\Model\Tty\TtyManager;

class AssertFileExistsHandler
{
    /**
     * @var Workspace
     */
    private $workspace;

    /**
     * @var TtyManager
     */
    private $ttyManager;

    public function __construct(Workspace $workspace, TtyManager $ttyManager)
    {
        $this->workspace = $workspace;
        $this->ttyManager = $ttyManager;
    }

    public function __invoke(AssertFileExists $job): Promise
    {
        $packageWorkspace = $this->workspace->package($job->package());
        $targetPath = $packageWorkspace->path().'/'.$job->path();

        $this->ttyManager->stdout($job->package()->ttyId())->writeln($job->description());

        if (!file_exists($targetPath)) {
            throw new JobFailure(sprintf('File "%s" does not exist', $job->path()));
        }

        return new Success(sprintf('File "%s" exists', $job->path()));
    }
}
