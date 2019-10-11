<?php

namespace Maestro\Extension\Vcs;

use Maestro\Extension\Report\ReportExtension;
use Maestro\Extension\Survey\SurveyExtension;
use Maestro\Extension\Task\TaskExtension;
use Maestro\Extension\Vcs\Extension\VcsDefinition;
use Maestro\Extension\Vcs\Report\VersionReport;
use Maestro\Extension\Vcs\Survey\VersionSurveyor;
use Maestro\Extension\Vcs\Task\CheckoutHandler;
use Maestro\Extension\Vcs\Task\CheckoutTask;
use Maestro\Extension\Vcs\Task\TagVersionHandler;
use Maestro\Extension\Vcs\Task\TagVersionTask;
use Maestro\Library\Instantiator\Instantiator;
use Maestro\Library\Vcs\RepositoryFactory;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\Console\ConsoleExtension;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\MapResolver\Resolver;
use RuntimeException;

class VcsExtension implements Extension
{
    const PARAM_TYPE = 'vcs.type';
    const TAG_REPOSITORY_FACTORY = 'vcs.repository_factory';

    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        $this->registerVcs($container);
        $this->registerTaskHandlers($container);
        $this->registerSurveyors($container);
        $this->registerReports($container);
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema)
    {
        $schema->setDefaults([
            self::PARAM_TYPE => 'git'
        ]);
    }

    private function registerTaskHandlers(ContainerBuilder $container)
    {
        $container->register(CheckoutHandler::class, function (Container $container) {
            return new CheckoutHandler($container->get(RepositoryFactory::class));
        }, [
            TaskExtension::TAG_TASK_HANDLER => [
                'alias' => 'checkout',
                'taskClass' => CheckoutTask::class
            ]
        ]);

        $container->register(TagVersionHandler::class, function (Container $container) {
            return new TagVersionHandler(
                $container->get(RepositoryFactory::class),
                $container->get(LoggingExtension::SERVICE_LOGGER)
            );
        }, [
            TaskExtension::TAG_TASK_HANDLER => [
                'alias' => 'tag',
                'taskClass' => TagVersionTask::class
            ]
        ]);
    }

    private function registerSurveyors(ContainerBuilder $container)
    {
        $container->register(VersionSurveyor::class, function (Container $container) {
            return new VersionSurveyor($container->get(RepositoryFactory::class));
        }, [
            SurveyExtension::TAG_SURVERYOR => []
        ]);
    }

    private function registerReports(ContainerBuilder $container)
    {
        $container->register(VersionReport::class, function (Container $container) {
            return new VersionReport(
                $container->get(ConsoleExtension::SERVICE_OUTPUT)
            );
        }, [
            ReportExtension::TAG_REPORT_CONSOLE => [
                'name' => 'version',
            ]
        ]);
    }

    private function registerVcs(ContainerBuilder $container)
    {
        $container->register(RepositoryFactory::class, function (Container $container) {
            $configuredType = $container->getParameter(self::PARAM_TYPE);
            $types = [];
        
            foreach ($container->getServiceIdsForTag(self::TAG_REPOSITORY_FACTORY) as $serviceId => $attrs) {
                $def = Instantiator::instantiate(VcsDefinition::class, array_merge([
                    'serviceId' => $serviceId,
                ], $attrs));
        
                if ($def->type() === $configuredType) {
                    return $container->get($def->serviceId());
                }
        
                $types[] = $def->type();
            }
        
            throw new RuntimeException(sprintf(
                'Unknown repository type "%s", known repository types "%s"',
                $configuredType,
                implode('", "', $types)
            ));
        });
    }
}
