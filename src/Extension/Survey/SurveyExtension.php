<?php

namespace Maestro\Extension\Survey;

use Maestro\Extension\Task\TaskExtension;
use Maestro\Library\Survey\Surveyors;
use Maestro\Extension\Survey\Task\SurveyHandler;
use Maestro\Extension\Survey\Task\SurveyTask;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\Logger\LoggingExtension;
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
                new Surveyors($surveyors),
                $container->get(LoggingExtension::SERVICE_LOGGER)
            );
        }, [ TaskExtension::TAG_TASK_HANDLER => [
            'alias' => 'survey',
            'taskClass' => SurveyTask::class,
        ]]);
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema)
    {
    }
}
