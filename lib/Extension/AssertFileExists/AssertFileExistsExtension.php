<?php

declare(strict_types=1);

namespace Maestro\Extension\AssertFileExists;

use Maestro\Extension\AssertFileExists\Job\AssertFileExists;
use Maestro\Extension\AssertFileExists\Job\AssertFileExistsHandler;
use Maestro\MaestroExtension;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\MapResolver\Resolver;

class AssertFileExistsExtension implements Extension
{
    private const SERVICE_ASSERT_FILE_EXISTS_HANDLER = 'maestro.adapter.assert_file_exists_handler';

    public function load(ContainerBuilder $container)
    {
        $container->register(self::SERVICE_ASSERT_FILE_EXISTS_HANDLER, function(Container $container) {
            return new AssertFileExistsHandler(
                $container->get(MaestroExtension::SERVICE_WORKSPACE),
                $container->get(MaestroExtension::SERVICE_CONSOLE_MANAGER)
            );
        }, [
            MaestroExtension::TAG_JOB_HANDLER => [
                'type' => 'assert-file-exists',
                'job' => AssertFileExists::class
            ]
        ]);
    }

    public function configure(Resolver $schema)
    {
        // no-op
    }
}
