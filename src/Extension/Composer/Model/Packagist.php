<?php

namespace Maestro\Extension\Composer\Model;

use Amp\Artax\Client;
use Amp\Artax\DefaultClient;
use Amp\Artax\Response;
use Amp\Promise;
use Composer\Semver\Semver;
use Composer\Semver\VersionParser;
use Maestro\Extension\Composer\Model\Exception\PackagistError;
use function Safe\json_decode;

class Packagist
{
    const URL_INFO = 'https://packagist.org/packages/%s.json';

    /**
     * @var Client
     */
    private $client;

    public function __construct(Client $client = null)
    {
        $this->client = $client ?: new DefaultClient();
    }

    public function packageInfo(string $name): Promise
    {
        return \Amp\call(function () use ($name) {
            if (!substr_count($name, '/') !== 1) {
                return new PackagistPackageInfo($name);
            }

            $response = yield $this->client->request(sprintf(self::URL_INFO, $name));

            assert($response instanceof Response);

            if ($response->getStatus() !== 200) {
                throw new PackagistError(sprintf(
                    'Packagist returned response code "%s"',
                    $response->getStatus()
                ));
            }

            $body = yield $response->getBody()->read();
            $info = array_merge([
                'package' => [
                    'versions' => [],
                ],
            ], json_decode($body, true));

            return new PackagistPackageInfo(
                $name,
                $this->latestVersion($info['package']['versions'])
            );
        });
    }

    private function latestVersion(array $versions)
    {
        $stableVersions = Semver::sort(array_filter(array_keys($versions), function (string $version) {
            return VersionParser::parseStability($version) === 'stable';
        }));

        if (empty($stableVersions)) {
            return '';
        }

        return $stableVersions[array_key_last($stableVersions)];
    }
}
