<?php

namespace Maestro\Tests\Unit\Library\Survey;

use Maestro\Library\Survey\Exception\ResultNotRegistered;
use Maestro\Library\Survey\Survey;
use Maestro\Library\Survey\SurveyResult;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class SurveyTest extends TestCase
{
    public function testReturnsResultByFqn()
    {
        $result = new class implements SurveyResult {
        };

        $survey = new Survey([
            $result
        ]);

        $this->assertSame($result, $survey->get(get_class($result)));
    }

    public function testThrowsExceptionIfResultIsNotAnObject()
    {
        $this->expectException(RuntimeException::class);
        $survey = new Survey([
            'hello'
        ]);
    }

    public function testThrowsExceptionIfResultNotSet()
    {
        $this->expectException(ResultNotRegistered::class);
        $survey = new Survey();
        $survey->get('Foobar');
    }
}
