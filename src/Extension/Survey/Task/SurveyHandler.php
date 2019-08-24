<?php

namespace Maestro\Extension\Survey\Task;

use Amp\Promise;
use Maestro\Extension\Survey\Model\SurveyBuilder;
use Maestro\Extension\Survey\Model\Surveyors;
use Maestro\Extension\Survey\Model\Surveyor;
use Maestro\Graph\Environment;
use Maestro\Graph\Task;
use Maestro\Graph\TaskHandler;

class SurveyHandler implements TaskHandler
{
    /**
     * @var Surveyors<Surveyor>
     */
    private $surveyors;

    public function __construct(Surveyors $surveyors)
    {
        $this->surveyors = $surveyors;
    }

    public function execute(Task $task, Environment $environment): Promise
    {
        return \Amp\call(function () use ($environment) {
            $surveyBuilder = new SurveyBuilder();
            foreach ($this->surveyors as $surveyor) {
                $result = yield $surveyor->survey($environment);
                if ($result === null) {
                    continue;
                }

                $surveyBuilder->addResult($result);
            }

            return $environment->builder()
               ->withVars([
                   'survey' => $surveyBuilder->build(),
               ])->build();
        });
    }
}
