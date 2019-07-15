<?php

namespace Maestro\Tests\Unit\Loader;

use Maestro\Loader\AliasToClassMap;
use Maestro\Loader\Exception\UnknownAlias;
use PHPUnit\Framework\TestCase;
use stdClass;

class AliasClassMapTest extends TestCase
{
    public function testExceptionOnUnknownAlias()
    {
        $this->expectException(UnknownAlias::class);
        (new AliasToClassMap([
            'foo' => stdClass::class
        ]))->classNameFor('bar');
    }
}
