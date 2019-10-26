<?php

namespace Maestro\Tests\Unit\Library\Loader\Loader;

use Maestro\Extension\Runner\Model\Loader\ManifestLoader;
use Maestro\Library\Loader\Loader\IncludingLoader;
use Maestro\Library\Loader\Loader\JsonLoader;
use Maestro\Tests\IntegrationTestCase;
use PHPUnit\Framework\TestCase;

class IncludingLoaderTest extends IntegrationTestCase
{
    /**
     * @var IncludingLoader
     */
    private $loader;

    protected function setUp(): void
    {
        $this->loader = new IncludingLoader(new JsonLoader());
    }

    /**
     * @dataProvider provideIncludingLoader
     */
    public function testIncludingLoader(string $manifest, array $expected)
    {
        $this->workspace()->loadManifest($manifest);
        $data = $this->loader->load($this->workspace()->path('config.json'));
        self::assertEquals($expected, $data);
    }

    public function provideIncludingLoader()
    {
        yield 'does not modify data without load key' => [
            <<<'EOT'
// File:config.json
{
    "foobar": "barfoo"
}
EOT
            ,
            ['foobar' => 'barfoo'],
        ];

        yield 'include file' => [
            <<<'EOT'
// File:config.json
{
    "foobar": "barfoo",
    "_include": "barfoo.json"
}
// File:barfoo.json
{
    "barfoo": "foobar"
}
EOT
            ,
            [
                'foobar' => 'barfoo',
                'barfoo' => 'foobar',
            ],
        ];

        yield 'include relative file' => [
            <<<'EOT'
// File:config.json
{
    "foobar": "barfoo",
    "_include": "barfoo/barfoo.json"
}
// File:barfoo/barfoo.json
{
    "_include": "../hello.json"
}
// File:hello.json
{
    "barfoo": "foobar"
}
EOT
            ,
            [
                'foobar' => 'barfoo',
                'barfoo' => 'foobar',
            ],
        ];

        yield 'process nested include' => [
            <<<'EOT'
// File:config.json
{
    "packages": {
        "_include": "barfoo/barfoo.json"
    }
}
// File:barfoo/barfoo.json
{
    "barfoo": "foobar"
}
EOT
            ,
            [
                'packages' => [
                    'barfoo'=> 'foobar',
                ],
            ],
        ];

        yield 'include glob' => [
            <<<'EOT'
// File:config.json
{
    "packages": {
        "_include": "packages/*.json"
    }
}
// File:packages/one.json
{
    "name": "one"
}
// File:packages/two.json
{
    "name": "two"
}
EOT
            ,
            [
                'packages' => [
                    [
                        'name' => 'one',
                    ],
                    [
                        'name' => 'two',
                    ],
                ],
            ],
        ];
    }
}
