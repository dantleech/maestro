<?php

namespace Maestro\Tests\Unit\Extension\Survey\Task;

use Amp\Success;
use Maestro\Extension\Survey\Task\SurveyHandler;
use Maestro\Extension\Survey\Task\SurveyTask;
use Maestro\Library\Survey\Surveyor;
use Maestro\Library\Survey\Surveyors;
use Maestro\Library\Artifact\Artifact;
use Maestro\Library\Artifact\Artifacts;
use Maestro\Library\Task\Test\HandlerTester;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class SurveyHandlerTest extends TestCase
{
    public function testNoHandlers()
    {
        $artifacts = HandlerTester::create(
            $this->createHandler(new Surveyors([]))
        )->handle(SurveyTask::class, [], [
            new Artifacts(),
        ]);

        $this->assertEmpty($artifacts);
    }

    public function testSurveys()
    {
        $surveyor = new class() implements Surveyor {
            public function description():string
            {
                return 'hello';
            }
            public function __invoke(Artifact $foobar)
            {
                $object = new TestArtifact();
                $object->foo = $foobar->bar;

                return new Success($object);
            }
        };

        $inputArtifact = new TestArtifact();
        $inputArtifact->bar = 'bar';

        $artifacts = HandlerTester::create(
            $this->createHandler(new Surveyors([
                $surveyor,
            ]))
        )->handle(SurveyTask::class, [], [
            new Artifacts([
                $inputArtifact
            ]),
        ]);

        $this->assertEquals('bar', $artifacts->get(TestArtifact::class)->foo);
    }

    private function createHandler(Surveyors $surveyors): SurveyHandler
    {
        return new SurveyHandler($surveyors, new NullLogger());
    }
}

class TestArtifact implements Artifact
{
    public $bar;
}
