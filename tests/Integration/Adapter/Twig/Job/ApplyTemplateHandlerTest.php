<?php

namespace Maestro\Tests\Integration\Adapter\Twig\Job;

use Maestro\Adapter\Twig\Job\ApplyTemplate;
use Maestro\Adapter\Twig\Job\ApplyTemplateHandler;
use Maestro\MaestroExtension;
use Maestro\Model\Package\Instantiator;
use Maestro\Model\Package\ManifestItem;
use Maestro\Model\Package\PackageDefinition;
use Maestro\Model\Package\PackageDefinitionBuilder;
use Maestro\Tests\Integration\IntegrationTestCase;

class ApplyTemplateHandlerTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        $this->workspace()->reset();
    }

    public function testRendersAndSavesTemplateToNewFile()
    {
        $this->workspace()->put('/test_template', 'Hello World');
        $definition = PackageDefinitionBuilder::create('foo/bar')->build();

        $this->handler()->__invoke(
            $this->createJob($definition, 'test_template')
        );

        self::assertFileExists($this->packageWorkspacePath('foo-bar/test_template'));
    }

    public function testCreatesTemplateAtNonExistingDirectory()
    {
        $this->workspace()->put('/sub-path/test_template', 'Hello World');
        $definition = PackageDefinitionBuilder::create('foo/bar')->build();

        $this->handler()->__invoke(
            $this->createJob($definition, 'sub-path/test_template')
        );

        self::assertFileExists($this->packageWorkspacePath('foo-bar/sub-path/test_template'));
    }

    public function testCreatesTemplateAtSpecifiedTargetPath()
    {
        $this->workspace()->put('test_template.twig', 'Hello World');
        $definition = PackageDefinitionBuilder::create('foo/bar')->build();

        ;
        $this->handler()->__invoke(
            $this->createJob($definition, 'test_template.twig', 'hello_world')
        );

        self::assertFileExists($this->packageWorkspacePath('foo-bar/hello_world'));
    }

    public function testPassesPackageParameters()
    {
        $this->workspace()->put('pass_params.twig', 'Hello {{ package.parameters.hello }}');

        $definition = Instantiator::create()->instantiate(PackageDefinition::class, [
            'name' => 'foobar/barfoo',
            'parameters' => [
                'hello' => 'goodbye',
            ]
        ]);

        $this->handler()->__invoke(
            $this->createJob($definition, 'pass_params.twig', 'hello_world')
        );

        $expectedTemplatePath = $this->packageWorkspacePath('foobar-barfoo/hello_world');
        $this->assertFileExists($expectedTemplatePath);
        $this->assertEquals('Hello goodbye', file_get_contents($expectedTemplatePath));
    }

    public function testMergesGlobalParameters()
    {
        $this->workspace()->put('global_param', 'Hello {{ package.parameters.hello }} {{ globalParameters.name }}');

        $definition = Instantiator::create()->instantiate(PackageDefinition::class, [
            'name' => 'foobar/barfoo',
            'parameters' => [
                'hello' => 'goodbye',
            ]
        ]);

        $this->handler([
            'name' => 'Daniel',
        ])->__invoke(
            $this->createJob($definition, 'global_param', 'hello_world')
        );

        $expectedTemplatePath = $this->packageWorkspacePath('foobar-barfoo/hello_world');
        $this->assertFileExists($expectedTemplatePath);
        $this->assertEquals('Hello goodbye Daniel', file_get_contents($expectedTemplatePath));
    }

    public function testPassesPackageDefinition()
    {
        $this->workspace()->put('package_def', 'I am {{ package.name }}');

        $definition = Instantiator::create()->instantiate(PackageDefinition::class, [
            'name' => 'foobar/barfoo'
        ]);

        $this->handler([
            'name' => 'Daniel',
        ])->__invoke(
            $this->createJob($definition, 'package_def', 'hello_world')
        );

        $expectedTemplatePath = $this->packageWorkspacePath('foobar-barfoo/hello_world');
        $this->assertFileExists($expectedTemplatePath);
        $this->assertEquals('I am foobar/barfoo', file_get_contents($expectedTemplatePath));
    }

    private function handler(array $globalParameters = []): ApplyTemplateHandler
    {
        $container = $this->container();
        $handler = new ApplyTemplateHandler(
            $container->get(MaestroExtension::SERVICE_CONSOLE_MANAGER),
            $container->get(MaestroExtension::SERVICE_WORKSPACE),
            $container->get(MaestroExtension::SERVICE_TWIG),
            $globalParameters
        );
        return $handler;
    }

    private function createJob(PackageDefinition $definition, string $sourcePath, string $targetPath = null): ApplyTemplate
    {
        $targetPath = $targetPath ?: $sourcePath;
        $job = new ApplyTemplate(
            $definition,
            Instantiator::create()->instantiate(
                ManifestItem::class,
                [
                    'type' => 'template',
                    'name' => $sourcePath,
                    'dest' => $targetPath
                ]
            )
        );
        return $job;
    }
}
