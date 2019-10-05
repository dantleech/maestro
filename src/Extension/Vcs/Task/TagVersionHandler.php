<?php

namespace Maestro\Extension\Vcs\Task;

use Amp\Promise;
use Amp\Success;
use Maestro\Library\Support\Package\Package;
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

    public function __invoke(Package $package, Workspace $workspace): Promise
    {
        $tagName = $package->version();

        if (null === $tagName) {
            return new Success([]);
        }

        $repository = $this->repositoryFactory->create($workspace->absolutePath());

        return \Amp\call(function () use ($package, $tagName, $repository) {
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
                'package' => $package->name(),
            ]);
            $repository->tag($tagName);

            return [];
        });
    }
}
