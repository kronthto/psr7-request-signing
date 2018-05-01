<?php

namespace Kronthto\Psr7RequestSignature;

interface RemoteAppInterface
{
    /**
     * Should provide the secret used for the HMAC function as a string.
     *
     * @return string
     */
    public function getSecret(): string;
}
