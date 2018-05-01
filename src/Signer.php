<?php

namespace Kronthto\Psr7RequestSignature;

use Psr\Http\Message\MessageInterface;

class Signer
{
    public const DEFAULT_ALGO = 'sha256';

    /**
     * @param string $payload
     * @param string $secret
     * @param string $algo
     *
     * @return string The calculated hash
     *
     * @throws \InvalidArgumentException In case an error occurs creating a hash for the given algo
     */
    public static function createHmacSignatureForPayload(
        string $payload,
        string $secret,
        string $algo = self::DEFAULT_ALGO
    ): string {
        $hash = @hash_hmac($algo, $payload, $secret);

        if (!$hash) {
            throw new \InvalidArgumentException('Could not calculate hash using algo '.$algo);
        }

        return $hash;
    }

    /**
     * @param string $payload
     * @param string $secret
     * @param string $algo
     *
     * @return string In format "algo=hash"
     *
     * @throws \InvalidArgumentException In case an error occurs creating a hash for the given algo
     */
    public static function createSignatureHeaderForPayload(
        string $payload,
        string $secret,
        string $algo = self::DEFAULT_ALGO
    ): string {
        return $algo.'='.static::createHmacSignatureForPayload($payload, $secret, $algo);
    }

    /**
     * Extract what of the Request is used to calculate the hash.
     *
     * @param MessageInterface $request
     *
     * @return string
     */
    public static function getPayloadOfRequestForHashing(MessageInterface $request): string
    {
        return (string) $request->getBody();
    }

    /**
     * @param MessageInterface $request
     * @param string           $secret
     * @param string           $algo
     *
     * @return string
     *
     * @throws \InvalidArgumentException In case an error occurs creating a hash for the given algo
     */
    public static function createSignatureHeaderForRequest(
        MessageInterface $request,
        string $secret,
        string $algo = self::DEFAULT_ALGO
    ): string {
        return static::createSignatureHeaderForPayload(static::getPayloadOfRequestForHashing($request), $secret, $algo);
    }

    /**
     * @param MessageInterface $request
     * @param string           $secret
     * @param string           $algo
     *
     * @return string
     *
     * @throws \InvalidArgumentException In case an error occurs creating a hash for the given algo
     */
    public static function getExpectedHashOfRequest(MessageInterface $request, string $secret, string $algo): string
    {
        return static::createHmacSignatureForPayload(static::getPayloadOfRequestForHashing($request), $secret, $algo);
    }
}
