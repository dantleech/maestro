<?php

namespace Maestro\Extension\Composer\Survery;

use Amp\Promise;
use Amp\Success;
use Maestro\Extension\Survey\Model\Surveyor;
use Maestro\Extension\Version\Survey\PackageResult;
use Maestro\Graph\Environment;
use Maestro\Loader\Instantiator;
use Webmozart\PathUtil\Path;
use function Safe\json_decode;
use function Safe\file_get_contents;

class ComposerSurveryor implements Surveyor
{
    const COMPOSER_JSON_FILE = 'composer.json';
    const ALIASED_BRANCH = 'dev-master';


    /**
     * {@inheritDoc}
     */
    public function survey(Environment $environment): Promise
    {
        $workspace = $environment->workspace();
        $composerPath = Path::join([$workspace->absolutePath(), self::COMPOSER_JSON_FILE]);

        if (false === file_exists($composerPath)) {
            return new Success(null);
        }

        $composer = json_decode(file_get_contents($composerPath), true);
        $info = [];

        if (isset($composer['extra'])) {
            $info = $this->processExtra($composer['extra'], $info);
        }

        return new Success(Instantiator::create()->instantiate(PackageResult::class, $info));
    }

    private function processExtra(array $data, array $info): array
    {
        if (isset($data['branch-alias'])) {
            if (isset($data['branch-alias'][self::ALIASED_BRANCH])) {
                $info['branchAlias'] = $data['branch-alias'][self::ALIASED_BRANCH];
            }
        }

        return $info;
    }
}
