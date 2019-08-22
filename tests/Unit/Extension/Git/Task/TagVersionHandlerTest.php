<?php

namespace Maestro\Tests\Unit\Extension\Git\Task;

use Amp\Success;
use Maestro\Extension\Git\Model\ExistingTag;
use Maestro\Extension\Git\Model\ExistingTags;
use Maestro\Extension\Git\Model\Git;
use Maestro\Extension\Git\Task\TagVersionHandler;
use Maestro\Extension\Git\Task\TagVersionTask;
use Maestro\Graph\Environment;
use Maestro\Graph\Test\HandlerTester;
use Maestro\Graph\Vars;
use Maestro\Loader\Instantiator;
use Maestro\Package\Package;
use Maestro\Workspace\Workspace;
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

    protected function setUp(): void
    {
        $this->git = $this->prophesize(Git::class);
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

        $env = $this->createEnv($package);
        $response = HandlerTester::create(
            $this->createHandler()
        )->handle(TagVersionTask::class, [], $env);

        $this->assertEquals(Environment::create($env), $response);
    }

    public function testReturnsEarlyIfTagAlreadyExists()
    {
        $package = $this->createPackage([
            'version' => '1.0.0',
        ]);
        $env = $this->createEnv($package);

        $this->git->listTags(
            self::EXAMPLE_PATH
        )->willReturn(new Success(new ExistingTags([
            new ExistingTag('1.0.0', '1234')
        ])));

        $response = HandlerTester::create(
            $this->createHandler()
        )->handle(TagVersionTask::class, [], $env);

        $this->assertEquals(Environment::create($env), $response);
        $this->logger->info(Argument::containingString('already exist'))->shouldBeCalled();
    }

    public function testTagsNewVersion()
    {
        $package = $this->createPackage([
            'version' => '1.0.1',
        ]);
        $env = $this->createEnv($package);

        $this->git->listTags(
            self::EXAMPLE_PATH
        )->willReturn(new Success(new ExistingTags([
            new ExistingTag('1.0.0', '1234')
        ])));

        $this->git->tag(
            self::EXAMPLE_PATH,
            '1.0.1'
        )->shouldBeCalled();

        HandlerTester::create(
            $this->createHandler()
        )->handle(TagVersionTask::class, [], $env);
    }

    private function createEnv(Package $package): array
    {
        return [
            'vars' => Vars::fromArray([
                'package' => $package,
            ]),
            'workspace' => $this->workspace,
        ];
    }

    private function createPackage(array $args): Package
    {
        $args['name'] = 'package1';
        return Instantiator::create()->instantiate(Package::class, $args);
    }

    private function createHandler(): TagVersionHandler
    {
        return new TagVersionHandler(
            $this->git->reveal(),
            $this->logger->reveal()
        );
    }
}
