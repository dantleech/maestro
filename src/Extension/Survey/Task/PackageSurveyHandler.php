<?php

namespace Maestro\Extension\Survey\Task;

use Amp\Promise;
use Maestro\Extension\Survey\Model\SurveyBuilder;
use Maestro\Extension\Survey\Model\Surveyors;
use Maestro\Extension\Survey\Model\Surveyor;
use Maestro\Graph\Environment;
use Maestro\Graph\Task;
use Maestro\Graph\TaskHandler;
use Maestro\Package\Package;

class PackageSurveyHandler implements TaskHandler
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
        $package = $environment->vars()->get('package');
        assert($package instanceof Package);

        return \Amp\call(function () use ($package, $environment) {
            $repoPath = $environment->workspace()->absolutePath();

            $surveyBuilder = new SurveyBuilder();
            foreach ($this->surveyors as $surveyor) {
                $surveyBuilder->addResult(yield $surveyor->survey($environment, $package));
            }

            return $environment->builder()
               ->withVars([
               ])->build();
        });
    }
}
