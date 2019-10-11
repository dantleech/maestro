<?php

namespace Maestro\Tests\Unit\Extension\Survey\Task;

use Amp\Success;
use Maestro\Extension\Survey\Task\SurveyHandler;
use Maestro\Extension\Survey\Task\SurveyTask;
use Maestro\Library\Survey\Survey;
use Maestro\Library\Survey\Surveyor;
use Maestro\Library\Survey\Surveyors;
use Maestro\Library\Task\Artifact;
use Maestro\Library\Task\Artifacts;
use Maestro\Library\Task\Test\HandlerTester;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use stdClass;

class SurveyHandlerTest extends TestCase
{
    public function testNoHandlers()
    {
        $artifacts = HandlerTester::create(
            $this->createHandler(new Surveyors([]))
        )->handle(SurveyTask::class, [], [
            new Artifacts(),
        ]);

        $survey = $artifacts->get(Survey::class);
        $this->assertInstanceOf(
            Survey::class,
            $survey
        );
        $this->assertEmpty($survey->toArray());
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

        $survey = $artifacts->get(Survey::class);
        $this->assertInstanceOf(Survey::class, $survey);
        $this->assertEquals('bar', $survey->get(TestArtifact::class)->foo);
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
