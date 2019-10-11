<?php

namespace Maestro\Extension\Survey\Task;

use Amp\Promise;
use Maestro\Library\Instantiator\Instantiator;
use Maestro\Library\Survey\SurveyBuilder;
use Maestro\Library\Survey\Surveyors;
use Maestro\Library\Survey\Surveyor;
use Maestro\Library\Artifact\Artifacts;
use Psr\Log\LoggerInterface;

class SurveyHandler
{
    /**
     * @var Surveyors<Surveyor>
     */
    private $surveyors;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(Surveyors $surveyors, LoggerInterface $logger)
    {
        $this->surveyors = $surveyors;
        $this->logger = $logger;
    }

    public function __invoke(SurveyTask $task, Artifacts $artifacts): Promise
    {
        return \Amp\call(function () use ($artifacts) {
            $surveyBuilder = new SurveyBuilder();

            foreach ($this->surveyors as $surveyor) {
                $this->logger->info('Making survey: ' . $surveyor->description());

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
