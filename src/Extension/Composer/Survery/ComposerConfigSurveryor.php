<?php

namespace Maestro\Extension\Composer\Survery;

use Amp\Promise;
use Amp\Success;
use Maestro\Library\Instantiator\Instantiator;
use Maestro\Library\Survey\Surveyor;
use Maestro\Library\Workspace\Workspace;
use Webmozart\PathUtil\Path;
use function Safe\json_decode;
use function Safe\file_get_contents;

class ComposerConfigSurveryor implements Surveyor
{
    const COMPOSER_JSON_FILE = 'composer.json';
    const ALIASED_BRANCH = 'dev-master';

    /**
     * {@inheritDoc}
     */
    public function __invoke(Workspace $workspace): Promise
    {
        $composerPath = Path::join([$workspace->absolutePath(), self::COMPOSER_JSON_FILE]);

        if (false === file_exists($composerPath)) {
            return new Success(null);
        }

        $composer = json_decode(file_get_contents($composerPath), true);

        $info = [];
        if (isset($composer['extra'])) {
            $info = $this->processExtra($composer['extra'], $info);
        }

        return new Success(Instantiator::instantiate(ComposerConfigResult::class, $info));
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

    /**
     * {@inheritDoc}
     */
    public function description(): string
    {
        return 'reading data from composer.json';
    }
}
