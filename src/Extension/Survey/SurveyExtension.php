<?php

namespace Maestro\Extension\Survey;

use Maestro\Extension\Maestro\MaestroExtension;
use Maestro\Extension\Survey\Model\Surveyors;
use Maestro\Extension\Survey\Task\SurveyHandler;
use Maestro\Extension\Survey\Task\SurveyTask;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\MapResolver\Resolver;

class SurveyExtension implements Extension
{
    const TAG_SURVERYOR = 'surveyor';

    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        $container->register(SurveyHandler::class, function (Container $container) {
            $surveyors = [];

            foreach (array_keys($container->getServiceIdsForTag(self::TAG_SURVERYOR)) as $serviceId) {
                $surveyors[] = $container->get($serviceId);
            }

            return new SurveyHandler(
                new Surveyors($surveyors)
            );
        }, [ MaestroExtension::TAG_JOB_HANDLER => [
            'alias' => 'survey',
            'job_class' => SurveyTask::class,
        ]]);
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema)
    {
    }
}
