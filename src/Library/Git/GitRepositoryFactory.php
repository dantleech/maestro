<?php

namespace Maestro\Library\Git;

use Maestro\Library\Script\ScriptRunner;
use Maestro\Library\Vcs\Repository;
use Maestro\Library\Vcs\RepositoryFactory;
use Psr\Log\LoggerInterface;

class GitRepositoryFactory implements RepositoryFactory
{
    /**
     * @var ScriptRunner
     */
    private $scriptRunner;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(ScriptRunner $scriptRunner, LoggerInterface $logger)
    {
        $this->scriptRunner = $scriptRunner;
        $this->logger = $logger;
    }

    public function create(string $path): Repository
    {
        return new GitRepository($this->scriptRunner, $this->logger, $path);
    }
}
