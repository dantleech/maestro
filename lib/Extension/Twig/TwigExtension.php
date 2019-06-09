<?php

namespace Maestro\Extension\Twig;

use Maestro\Extension\Maestro\MaestroExtension;
use Maestro\Extension\Twig\Task\TemplateHandler;
use Maestro\Extension\Twig\Task\TemplateTask;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\MapResolver\Resolver;

class TwigExtension implements Extension
{
    public const PARAM_TEMPLATE_PATHS = 'template_paths';

    private const SERVICE_APPLY_TEMPLATE_HANDLER = 'maestro.adapter.twig.handler.apply_template';
    const SERVICE_TWIG_FACTORY = 'twig_factory';

    public function configure(Resolver $schema)
    {
    }

    public function load(ContainerBuilder $container)
    {
        $container->register(self::SERVICE_APPLY_TEMPLATE_HANDLER, function (Container $container) {
            return new TemplateHandler($container->get(self::SERVICE_TWIG_FACTORY));
        }, [ MaestroExtension::TAG_JOB_HANDLER => [
            'alias' => 'template',
            'job_class' => TemplateTask::class,
        ]]);

        $container->register(self::SERVICE_TWIG_FACTORY, function (Container $container) {
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
