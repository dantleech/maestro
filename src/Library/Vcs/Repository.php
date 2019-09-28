<?php

namespace Maestro\Library\Vcs;

use Amp\Promise;

interface Repository
{
    /**
     * @return Promise<void>
     */
    public function checkout(string $url): Promise;

    /**
     * @return Promise<Tags>
     */
    public function listTags(): Promise;

    /**
     * @return Promise<void>
     */
    public function tag(string $name): Promise;

    /**
     * @return Promise<string>
     */
    public function headId(): Promise;

    /**
     * @return Promise<string[]>
     */
    public function commitsBetween(string $id1, string $id2): Promise;
}
