<?php

namespace Maestro\Tests\Unit\Extension\Composer\Model;

use Amp\Artax\Client;
use Amp\Artax\DnsException;
use Amp\Artax\Response;
use Amp\ByteStream\InMemoryStream;
use Amp\ByteStream\Message;
use Amp\Success;
use Maestro\Extension\Composer\Model\Exception\PackagistDnsError;
use Maestro\Extension\Composer\Model\Exception\PackagistError;
use Maestro\Extension\Composer\Model\Packagist;
use Maestro\Extension\Composer\Model\PackagistPackageInfo;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class PackagistTest extends TestCase
{
    /**
     * @var ObjectProphecy|Client
     */
    private $client;

    /**
     * @var Packagist
     */
    private $packagist;

    /**
     * @var ObjectProphecy
     */
    private $response;

    protected function setUp(): void
    {
        $this->client = $this->prophesize(Client::class);
        $this->packagist = new Packagist($this->client->reveal());
        $this->response = $this->prophesize(Response::class);
    }

    public function testThrowsExceptionOnInvalidName()
    {
        $this->expectException(PackagistError::class);
        $this->packageInfo('nothisisnot');
    }

    public function testThrowsExceptionOnDnsFailure()
    {
        $this->expectException(PackagistDnsError::class);
        $this->client->request(Argument::any())->willThrow(new DnsException('nope'));
        $this->packageInfo('hello/goodbye');
    }

    public function testThrowsExceptionIfResponseIsNot200()
    {
        $this->expectException(PackagistError::class);

        $this->response->getStatus()->willReturn(500);
        $this->client->request(Argument::any())->willReturn(
            new Success($this->response->reveal())
        );
        $this->packageInfo('hello/goodbye');
    }

    public function testThrowsExceptionJsonIsNotCorrect()
    {
        $this->expectException(PackagistError::class);

        $this->response->getStatus()->willReturn(200);
        $this->response->getBody()->willReturn(new Message(new InMemoryStream(
            ' i am not valid json '
        )));
        $this->client->request(Argument::any())->willReturn(
            new Success($this->response->reveal())
        );
        $this->packageInfo('hello/goodbye');
    }

    public function testWhenJsonIsUnexpectedEmptyInfoIsReturned()
    {
        $this->response->getStatus()->willReturn(200);
        $this->response->getBody()->willReturn(new Message(new InMemoryStream(
            '{"packa": {}}'
        )));
        $this->client->request(Argument::any())->willReturn(
            new Success($this->response->reveal())
        );
        $info = $this->packageInfo('hello/goodbye');
        $this->assertInstanceOf(PackagistPackageInfo::class, $info);
    }

    /**
     * @dataProvider provideReturnsLatestVersion
     */
    public function testReturnsLatestVersion(array $versions, string $expectedVersion)
    {
        $this->response->getStatus()->willReturn(200);
        $this->response->getBody()->willReturn(new Message(new InMemoryStream(json_encode([
            'package' => [
                'versions' => $versions
            ],
        ]))));
        $this->client->request(Argument::any())->willReturn(
            new Success($this->response->reveal())
        );
        $info = $this->packageInfo('hello/goodbye');
        $this->assertInstanceOf(PackagistPackageInfo::class, $info);
        $this->assertEquals($expectedVersion, $info->latestVersion());
    }

    public function provideReturnsLatestVersion()
    {
        yield 'no versions' => [
            [],
            ''
        ];

        yield 'one version' => [
            [
                '1.0.0' => [
                    'version' => '1.0.0',
                ],
            ],
            '1.0.0'
        ];

        yield 'unordered version' => [
            [
                '1.0.0' => [
                ],
                '2.0.0' => [
                ],
                '0.1.0' => [
                ],
            ],
            '2.0.0'
        ];

        yield 'filters out dev versions' => [
            [
                'dev-foo' => [
                ],
                'dev-bar' => [
                ],
                '1.0.0' => [
                ],
                '0.1.0' => [
                ],
            ],
            '1.0.0'
        ];

        yield 'only dev versions returns empty string' => [
            [
                'dev-foo' => [
                ],
                'dev-bar' => [
                ],
            ],
            ''
        ];
    }

    private function packageInfo(string $string)
    {
        return \Amp\Promise\wait(
            $this->packagist->packageInfo($string)
        );
    }
}
