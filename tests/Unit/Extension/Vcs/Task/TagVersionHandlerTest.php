<?php

namespace Maestro\Tests\Unit\Extension\Vcs\Task;

use Amp\Success;
use Maestro\Extension\Vcs\Task\TagVersionHandler;
use Maestro\Extension\Vcs\Task\TagVersionTask;
use Maestro\Library\Instantiator\Instantiator;
use Maestro\Library\Support\Package\Package;
use Maestro\Library\Task\Test\HandlerTester;
use Maestro\Library\Vcs\Repository;
use Maestro\Library\Vcs\RepositoryFactory;
use Maestro\Library\Vcs\Tag;
use Maestro\Library\Vcs\Tags;
use Maestro\Library\Workspace\Workspace;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class TagVersionHandlerTest extends TestCase
{
    const EXAMPLE_PATH = '/path/to';

    /**
     * @var ObjectProphecy
     */
    private $git;

    /**
     * @var NullLogger|ObjectProphecy
     */
    private $logger;

    /**
     * @var Workspace
     */
    private $workspace;

    /**
     * @var ObjectProphecy
     */
    private $repository;

    /**
     * @var ObjectProphecy
     */
    private $repositoryFactory;

    protected function setUp(): void
    {
        $this->repository = $this->prophesize(Repository::class);
        $this->repositoryFactory = $this->prophesize(RepositoryFactory::class);
        $this->repositoryFactory->create(self::EXAMPLE_PATH)->wilLReturn($this->repository);
        $this->logger = $this->prophesize(LoggerInterface::class);

        $this->workspace = new Workspace(
            self::EXAMPLE_PATH,
            'workspace one'
        );
    }

    public function testIsSuccessfulPackageHasNoVersion()
    {
        $package = $this->createPackage([
            'version' => null,
        ]);

        $artifacts = HandlerTester::create(
            $this->createHandler()
        )->handle(TagVersionTask::class, [], [
            $package,
            $this->workspace
        ]);

        $this->assertEquals([], $artifacts->toArray());
    }

    public function testReturnsEarlyIfTagAlreadyExists()
    {
        $package = $this->createPackage([
            'version' => '1.0.0',
        ]);

        $this->repository->listTags()->willReturn(new Success(new Tags([
            new Tag('1.0.0', '1234')
        ])));

        $response = HandlerTester::create(
            $this->createHandler()
        )->handle(TagVersionTask::class, [], [
            $package,
            $this->workspace
        ]);

        $this->logger->info(Argument::containingString('already exist'))->shouldBeCalled();
    }

    public function testTagsNewVersion()
    {
        $package = $this->createPackage([
            'version' => '1.0.1',
        ]);

        $this->repository->listTags()->willReturn(new Success(new Tags([
            new Tag('1.0.0', '1234')
        ])));

        $this->repository->tag(
            '1.0.1'
        )->shouldBeCalled();

        HandlerTester::create(
            $this->createHandler()
        )->handle(TagVersionTask::class, [], [
            $package,
            $this->workspace
        ]);
    }

    private function createPackage(array $args): Package
    {
        $args['name'] = 'package1';
        return Instantiator::instantiate(Package::class, $args);
    }

    private function createHandler(): TagVersionHandler
    {
        return new TagVersionHandler(
            $this->repositoryFactory->reveal(),
            $this->logger->reveal()
        );
    }
}
