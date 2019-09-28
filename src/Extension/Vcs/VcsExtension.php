<?php

namespace Maestro\Extension\Vcs;

use Maestro\Extension\Task\TaskExtension;
use Maestro\Extension\Vcs\Extension\VcsDefinition;
use Maestro\Extension\Vcs\Task\CheckoutHandler;
use Maestro\Extension\Vcs\Task\CheckoutTask;
use Maestro\Library\Instantiator\Instantiator;
use Maestro\Library\Vcs\RepositoryFactory;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
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

        $container->register(CheckoutHandler::class, function (Container $container) {
            return new CheckoutHandler($container->get(RepositoryFactory::class));
        }, [
            TaskExtension::TAG_TASK_HANDLER => [
                'alias' => 'checkout',
                'taskClass' => CheckoutTask::class
            ]
        ]);
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
}
