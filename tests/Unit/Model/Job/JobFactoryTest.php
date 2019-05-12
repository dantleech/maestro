<?php

namespace Maestro\Tests\Unit\Model\Job;

use Maestro\Model\Job\Exception\JobNotFound;
use Maestro\Model\Job\Job;
use Maestro\Model\Job\JobFactory;
use PHPUnit\Framework\TestCase;
use stdClass;

class JobFactoryTest extends TestCase
{
    public function testThrowsExceptionIfJobNotFound()
    {
        $this->expectException(JobNotFound::class);
        $this->createFactory([
            'foo' => stdClass::class,
        ])->create('bar', [], []);
    }

    public function testCreatesJob()
    {
        $job = $this->createFactory([
            'foo' => TestJob1::class,
        ])->create('foo', [
            'hello' => 'goodbye'
        ], []);

        $this->assertInstanceOf(Job::class, $job);
        $this->assertEquals('goodbye', $job->hello());
    }

    public function testCreatesJobOptionalParametersAreIgnoredIfNotDefinedInJobConstructor()
    {
        $job = $this->createFactory([
            'foo' => TestJob1::class,
        ])->create('foo', [
            'hello' => 'goodbye',
        ], [
            'goodebye' => 'goodbye'
        ]);

        $this->assertInstanceOf(Job::class, $job);
        $this->assertEquals('goodbye', $job->hello());
    }

    public function testCreatesJobOptionalParametersArePassedIfDefinedInJobConstructor()
    {
        $job = $this->createFactory([
            'foo' => TestJob1::class,
        ])->create('foo', [
            'hello' => 'goodbye',
        ], [
            'bar' => 'foo'
        ]);

        $this->assertInstanceOf(Job::class, $job);
        $this->assertEquals('goodbye', $job->hello());
        $this->assertEquals('foo', $job->bar());
    }

    private function createFactory(array $jobClassMap)
    {
        return new JobFactory($jobClassMap);
    }
}

class TestJob1 implements Job
{
    /**
     * @var string
     */
    private $hello;
    /**
     * @var string
     */
    private $bar;

    public function __construct(string $hello, string $bar = 'bar')
    {
        $this->hello = $hello;
        $this->bar = $bar;
    }

    public function hello(): string
    {
        return $this->hello;
    }

    public function bar(): string
    {
        return $this->bar;
    }

    public function description(): string
    {
        return 'HEY!';
    }
}
