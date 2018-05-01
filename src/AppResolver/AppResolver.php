<?php

namespace Kronthto\Psr7RequestSignature\AppResolver;

use Kronthto\Psr7RequestSignature\RemoteAppInterface;

interface AppResolver
{
    /**
     * Try to resolve the given RemoteApp ID.
     *
     * @param mixed $id
     *
     * @return RemoteAppInterface|null Should return null if not found
     */
    public function findRemoteAppById($id): ?RemoteAppInterface;
}
