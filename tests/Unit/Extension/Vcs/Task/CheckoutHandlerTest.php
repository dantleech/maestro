<?php

namespace Maestro\Tests\Unit\Extension\Vcs\Task;

use Amp\Success;
use Maestro\Extension\Vcs\Task\CheckoutHandler;
use Maestro\Extension\Vcs\Task\CheckoutTask;
use Maestro\Library\Support\Environment\Environment;
use Maestro\Library\Task\Test\HandlerTester;
use Maestro\Library\Vcs\Repository;
use Maestro\Library\Vcs\RepositoryFactory;
use Maestro\Library\Workspace\Workspace;
use PHPUnit\Framework\TestCase;

class CheckoutHandlerTest extends TestCase
{
    const EXAMPLE_REPO_URL = 'http://example.com/repo';
    const EXAMPLE_WORKSPACE_PATH = '/path/to';

    /**
     * @var ObjectProphecy
     */
    private $repositoryFactory;

    /**
     * @var CheckoutHandler
     */
    private $checkoutHandler;

    /**
     * @var ObjectProphecy
     */
    private $repository;

    protected function setUp(): void
    {
        $this->repositoryFactory = $this->prophesize(RepositoryFactory::class);
        $this->repository = $this->prophesize(Repository::class);
        $this->checkoutHandler = new CheckoutHandler($this->repositoryFactory->reveal());
    }

    public function testItIgnoresExistingRepository()
    {
        $this->repositoryFactory->create(self::EXAMPLE_WORKSPACE_PATH)->willReturn($this->repository->reveal());
        $this->repository->isCheckedOut()->willReturn(true);

        $artifacts = HandlerTester::create($this->checkoutHandler)->handle(CheckoutTask::class, [
            'url' => self::EXAMPLE_REPO_URL,
        ], [
            new Workspace(self::EXAMPLE_WORKSPACE_PATH, 'name'),
            new Environment([]),
        ]);

        $this->assertCount(0, $artifacts);
    }

    public function testChecksoutRepository()
    {
        $this->repositoryFactory->create(self::EXAMPLE_WORKSPACE_PATH)->willReturn($this->repository->reveal());
        $this->repository->isCheckedOut()->willReturn(false);
        $this->repository->checkout(self::EXAMPLE_REPO_URL, new Environment([]))->willReturn(new Success());

        $artifacts = HandlerTester::create($this->checkoutHandler)->handle(CheckoutTask::class, [
            'url' => self::EXAMPLE_REPO_URL,
        ], [
            new Workspace(self::EXAMPLE_WORKSPACE_PATH, 'name'),
            new Environment([]),
        ]);

        $this->assertCount(0, $artifacts);
    }
}
