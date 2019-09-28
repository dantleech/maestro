<?php

namespace Maestro\Extension\Vcs\Task;

use Amp\Promise;
use Maestro\Library\Support\Environment\Environment;
use Maestro\Library\Support\Package\Package;
use Maestro\Library\Task\Exception\TaskFailure;
use Maestro\Library\Vcs\Exception\CheckoutError;
use Maestro\Library\Vcs\Repository;
use Maestro\Library\Vcs\RepositoryFactory;
use Maestro\Library\Workspace\Workspace;

class CheckoutHandler
{
    /**
     * @var RepositoryFactory
     */
    private $repositoryFactory;

    public function __construct(RepositoryFactory $repositoryFactory)
    {
        $this->repositoryFactory = $repositoryFactory;
    }

    public function __invoke(CheckoutTask $checkoutTask, Workspace $workspace, Environment $environment): Promise
    {
        return \Amp\call(function () use ($checkoutTask, $workspace, $environment) {
            $repository = $this->repositoryFactory->create($workspace->absolutePath());

            if ($repository->isCheckedOut()) {
                return [];
            }

            $workspace->purge();

            try {
                yield $repository->checkout($checkoutTask->url(), $environment);
            } catch (CheckoutError $e) {
                throw new TaskFailure($e->getMessage());
            }

            return [];
        });
    }
}
