<?php

namespace Maestro\Tests\Unit\Library\Survey;

use Maestro\Library\Survey\Exception\ResultNotRegistered;
use Maestro\Library\Survey\Survey;
use Maestro\Library\Task\Artifact;
use PHPUnit\Framework\TestCase;

class SurveyTest extends TestCase
{
    public function testReturnsResultByFqn()
    {
        $result = new class implements Artifact {
        };

        $survey = new Survey([
            $result
        ]);

        $this->assertSame($result, $survey->get(get_class($result)));
    }

    public function testThrowsExceptionIfResultNotSet()
    {
        $this->expectException(ResultNotRegistered::class);
        $survey = new Survey();
        $survey->get('Foobar');
    }
}
