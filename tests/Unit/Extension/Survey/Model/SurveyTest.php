<?php

namespace Maestro\Tests\Unit\Extension\Survey\Model;

use Maestro\Extension\Survey\Model\Exception\ResultNotRegistered;
use Maestro\Extension\Survey\Model\Survey;
use Maestro\Extension\Survey\Model\SurveyResult;
use PHPUnit\Framework\TestCase;

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

    public function testThrowsExceptionIfResultNotSet()
    {
        $this->expectException(ResultNotRegistered::class);
        $survey = new Survey();
        $survey->get('Foobar');
    }
}
