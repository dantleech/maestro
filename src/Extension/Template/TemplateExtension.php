<?php

namespace Maestro\Extension\Template;

use Maestro\Extension\Maestro\MaestroExtension;
use Maestro\Extension\Runner\RunnerExtension;
use Maestro\Extension\Template\Task\TemplateHandler;
use Maestro\Extension\Template\Task\TemplateTask;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\MapResolver\Resolver;
use Maestro\Extension\Template\EnvironmentFactory;

class TemplateExtension implements Extension
{
    public const PARAM_TEMPLATE_PATHS = 'template_paths';

    public function configure(Resolver $schema)
    {
    }

    public function load(ContainerBuilder $container)
    {
        $container->register(TemplateHandler::class, function (Container $container) {
            return new TemplateHandler($container->get(EnvironmentFactory::class));
        }, [ RunnerExtension::TAG_TASK_HANDLER => [
            'alias' => 'template',
            'taskClass' => TemplateTask::class,
        ]]);

        $container->register(EnvironmentFactory::class, function (Container $container) {
            return new EnvironmentFactory(
                [
                    'strict_variables' => true,
                    'auto_reload' => false,
                    'cache' => false,
                ]
            );
        });
    }
}
