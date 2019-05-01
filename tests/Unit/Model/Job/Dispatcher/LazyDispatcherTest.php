<?php

namespace Maestro\Tests\Unit\Model\Job\Dispatcher;

use Amp\Promise;
use Amp\Success;
use Maestro\Model\Job\Dispatcher\LazyDispatcher;
use Maestro\Model\Job\Exception\HandlerNotFound;
use Maestro\Model\Job\Exception\InvalidHandler;
use Maestro\Model\Job\Job;
use Maestro\Model\Job\JobDispatcher;
use PHPUnit\Framework\TestCase;
use stdClass;

class LazyDispatcherTest extends TestCase
{
    /**
     * @var ObjectProphecy
     */
    private $job;

    protected function setUp(): void
    {
        $this->job = $this->prophesize(Job::class);
    }

    public function testThrowsExceptionIfHandlerNotFound()
    {
        $this->expectException(HandlerNotFound::class);
        $this->create(['foobar' => function () {
        }])->dispatch($this->job->reveal());
    }

    public function testThrowsExceptionIfMappedFactoryIsNotAClosure()
    {
        $this->expectException(InvalidHandler::class);
        $this->expectExceptionMessage('must return a Closure');

        $this->create([
            get_class($this->job->reveal()) => 'no'
        ])->dispatch($this->job->reveal());
    }

    public function testThrowsExceptionIfHandlerIsNotCallable()
    {
        $this->expectException(InvalidHandler::class);
        $this->expectExceptionMessage('did not return a callable');
        $this->create([
            get_class($this->job->reveal()) => function () {
                return new \stdClass();
            }
        ])->dispatch($this->job->reveal());
    }

    public function testThrowsExceptionIfHandlerDoesNotReturnAPromise()
    {
        $this->expectException(InvalidHandler::class);
        $this->expectExceptionMessage('must return an Amp\Promise');

        $this->create([
            get_class($this->job->reveal()) => function () {
                return new class {
                    public function __invoke(Job $job)
                    {
                        return new stdClass();
                    }
                };
            }
        ])->dispatch($this->job->reveal());
    }

    public function testDispatchesJob()
    {
        $promise = $this->create([
            get_class($this->job->reveal()) => function () {
                return new class() {
                    public function __invoke(Job $job)
                    {
                        return new Success();
                    }
                };
            }
        ])->dispatch($this->job->reveal());
        $this->assertInstanceOf(Promise::class, $promise);
    }

    public function create(array $map): JobDispatcher
    {
        return new LazyDispatcher($map);
    }
}
