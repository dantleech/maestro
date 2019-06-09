<?php

namespace Maestro\Extension\Twig;

use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\MapResolver\Resolver;

class TwigExtension implements Extension
{
    public const SERVICE_TWIG = 'maestro.twig';
    private const SERVICE_APPLY_TEMPLATE_HANDLER = 'maestro.adapter.twig.handler.apply_template';
    public const PARAM_TEMPLATE_PATHS = 'template_paths';

    public function configure(Resolver $schema)
    {
        $schema->setDefaults([
            self::PARAM_TEMPLATE_PATHS => []
        ]);
    }

    public function load(ContainerBuilder $container)
    {
        $container->register(self::SERVICE_APPLY_TEMPLATE_HANDLER, function (Container $container) {
            return new ApplyTemplateHandler(
                $container->get(MaestroExtension::SERVICE_CONSOLE_MANAGER),
                $container->get(MaestroExtension::SERVICE_WORKSPACE),
                $container->get(self::SERVICE_TWIG),
                $container->getParameter(MaestroExtension::PARAM_PARAMETERS)
            );
        }, [
            MaestroExtension::TAG_JOB_HANDLER => [
                'type' => 'template',
                'job' => ApplyTemplate::class
            ]
        ]);

        $container->register(self::SERVICE_TWIG, function (Container $container) {
            return new Environment(
                new FilesystemLoader($this->resolveTemplatePaths($container)),
                [
                    'strict_variables' => true,
                    'auto_reload' => false,
                    'cache' => false,
                ]
            );
        });
    }

    private function resolveTemplatePaths(Container $container)
    {
        return array_merge(
            [ $container->getParameter(MaestroExtension::PARAM_CONFIG_DIR) ],
            $container->getParameter(self::PARAM_TEMPLATE_PATHS)
        );
    }
}
