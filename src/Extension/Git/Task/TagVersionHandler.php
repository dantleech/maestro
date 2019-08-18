<?php

namespace Maestro\Extension\Git\Task;

use Amp\Promise;
use Amp\Success;
use Maestro\Extension\Git\Model\Git;
use Maestro\Graph\Environment;
use Maestro\Graph\Task;
use Maestro\Graph\TaskHandler;
use Psr\Log\LoggerInterface;

class TagVersionHandler implements TaskHandler
{
    /**
     * @var Git
     */
    private $git;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(Git $git, LoggerInterface $logger)
    {
        $this->git = $git;
        $this->logger = $logger;
    }

    public function execute(Task $task, Environment $environment): Promise
    {
        $tagName = $environment->vars()->get('package')->version();

        if (null === $tagName) {
            return new Success($environment);
        }

        return \Amp\call(function () use ($tagName, $environment) {
            $path = $environment->workspace()->absolutePath();
            $env = $environment->env()->toArray();
            $existingTags = yield $this->git->listTags($environment->workspace()->absolutePath());
            if (in_array($tagName, $existingTags)) {
                $this->logger->info(sprintf('Git tag "%s" already exists (%s)', $tagName, implode(', ', $existingTags)));
                return $environment;
            }

            $this->git->tag($environment->workspace()->absolutePath(), $tagName);

            return $environment;
        });
    }
}
