<?php

namespace Maestro\Extension\Vcs\Task;

use Amp\Promise;
use Amp\Success;
use Maestro\Library\Vcs\RepositoryFactory;
use Maestro\Library\Vcs\Tags;
use Maestro\Library\Workspace\Workspace;
use Psr\Log\LoggerInterface;

class TagVersionHandler
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var RepositoryFactory
     */
    private $repositoryFactory;

    public function __construct(RepositoryFactory $repositoryFactory, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->repositoryFactory = $repositoryFactory;
    }

    public function __invoke(TagVersionTask $tagTask, Workspace $workspace): Promise
    {
        $tagName = $tagTask->tag();

        if (!$tagName) {
            return new Success([]);
        }

        $repository = $this->repositoryFactory->create($workspace->absolutePath());

        return \Amp\call(function () use ($tagName, $repository, $workspace) {
            $existingTags = yield $repository->listTags();
            assert($existingTags instanceof Tags);

            if ($existingTags->has($tagName)) {
                $this->logger->info(sprintf(
                    'Git tag "%s" already exists (%s)',
                    $tagName,
                    implode(', ', $existingTags->names())
                ));
                return [];
            }

            $this->logger->info(sprintf('Tagging version "%s"', $tagName), [
                'workspace' => $workspace->name(),
            ]);
            $repository->tag($tagName);

            return [];
        });
    }
}
