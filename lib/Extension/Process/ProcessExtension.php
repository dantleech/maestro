<?php

namespace Maestro\Extension\Process;

use Maestro\Extension\Process\Job\Checkout;
use Maestro\Extension\Process\Job\CheckoutHandler;
use Maestro\Extension\Process\Job\PackageProcess;
use Maestro\Extension\Process\Job\PackageProcessHandler;
use Maestro\Extension\Process\Job\Process;
use Maestro\Extension\Process\Job\ProcessHandler;
use Maestro\MaestroExtension;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\MapResolver\Resolver;

class ProcessExtension implements Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        $container->register('maestro.adapter.amp.handler.process', function (Container $container) {
            return new ProcessHandler(
                $container->get(MaestroExtension::SERVICE_CONSOLE_MANAGER)
            );
        }, [ MaestroExtension::TAG_JOB_HANDLER => [
            'job' => Process::class
        ]]);

        $container->register('maestro.adapter.amp.handler.checkout', function (Container $container) {
            return new CheckoutHandler(
                $container->get(MaestroExtension::SERVICE_WORKSPACE)
            );
        }, [ MaestroExtension::TAG_JOB_HANDLER => [
            'type' => 'checkout',
            'job' => Checkout::class,
        ]]);

        $container->register('maestro.adapter.amp.handler.package_command', function (Container $container) {
            return new PackageProcessHandler(
                $container->get(MaestroExtension::SERVICE_WORKSPACE)
            );
        }, [ MaestroExtension::TAG_JOB_HANDLER => [
            'type' => 'command',
            'job' => PackageProcess::class,
        ]]);
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema)
    {
    }
}
