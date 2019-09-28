<?php

namespace Maestro\Library\Vcs;

use Amp\Promise;
use Maestro\Library\Vcs\Exception\CheckoutError;

interface Repository
{
    /**
     * @throws CheckoutError
     * @promise-return Promise<void>
     */
    public function checkout(string $url, array $environment = []): Promise;

    /**
     * @promise-return Promise<Tags>
     */
    public function listTags(): Promise;

    /**
     * @promise-return Promise<void>
     */
    public function tag(string $name): Promise;

    /**
     * @promise-return Promise<string>
     */
    public function headId(): Promise;

    /**
     * @promise-return Promise<string[]>
     */
    public function commitsBetween(string $id1, string $id2): Promise;

    public function isCheckedOut(): bool;
}
