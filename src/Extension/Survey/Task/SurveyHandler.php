<?php

namespace Maestro\Extension\Survey\Task;

use Amp\Promise;
use Maestro\Library\Instantiator\Instantiator;
use Maestro\Library\Survey\SurveyBuilder;
use Maestro\Library\Survey\Surveyors;
use Maestro\Library\Survey\Surveyor;
use Maestro\Library\Task\Artifacts;

class SurveyHandler
{
    /**
     * @var Surveyors<Surveyor>
     */
    private $surveyors;

    public function __construct(Surveyors $surveyors)
    {
        $this->surveyors = $surveyors;
    }

    public function __invoke(SurveyTask $task, Artifacts $artifacts): Promise
    {
        return \Amp\call(function () use ($artifacts) {
            $surveyBuilder = new SurveyBuilder();

            foreach ($this->surveyors as $surveyor) {
                $result = yield Instantiator::call(
                    $surveyor,
                    '__invoke',
                    $artifacts->toArray(),
                    Instantiator::MODE_TYPE
                );
                if ($result === null) {
                    continue;
                }

                $surveyBuilder->addResult($result);
            }

            return [
                $surveyBuilder->build(),
            ];
        });
    }
}
