<?php

namespace Kronthto\Psr7RequestSignature;

use Kronthto\Psr7RequestSignature\AppResolver\AppResolver;
use Psr\Http\Message\MessageInterface;

class Verifier
{
    public const DEFAULT_ID_HEADER = 'X-RemoteApp-ID';
    public const DEFAULT_SIG_HEADER = 'X-RemoteApp-Signature';

    protected $opts = [
        'id_header' => self::DEFAULT_ID_HEADER,
        'sig_header' => self::DEFAULT_SIG_HEADER,
    ];
    /** @var AppResolver */
    protected $remoteAppResolver;

    public function getRemoteAppIdHeaderName(): string
    {
        return $this->opts['id_header'];
    }

    public function getSignatureHeaderName(): string
    {
        return $this->opts['sig_header'];
    }

    public function __construct(AppResolver $appResolver, array $opts = [])
    {
        $this->remoteAppResolver = $appResolver;
        $this->opts = array_merge($this->opts, $opts);
    }

    /**
     * Checks that the request contains a valid signature and returns the RemoteApp if so.
     *
     * @param MessageInterface $request
     *
     * @return RemoteAppInterface
     *
     * @throws AuthenticationException
     */
    public function validateRemoteAppRequest(MessageInterface $request): RemoteAppInterface
    {
        $remoteApp = $this->findRemoteAppByRequest($request);
        if (!$remoteApp) {
            throw new AuthenticationException('Request signature App-ID not found');
        }

        $givenSignature = $this->parseSignature($this->getGivenSignature($request));
        if (\count($givenSignature) !== 2) {
            throw new AuthenticationException('Request signature malformed');
        }

        if (!hash_equals(
            $this->generateExpectedSignature($request, $remoteApp, $givenSignature[0]),
            $givenSignature[1]
        )) {
            throw new AuthenticationException('Request signature invalid');
        }

        return $remoteApp;
    }

    protected function parseSignature(?string $signature): array
    {
        if (!$signature) {
            throw new AuthenticationException('Request does not contain a signature');
        }

        return explode('=', $signature, 2);
    }

    protected function getGivenSignature(MessageInterface $request): ?string
    {
        $headers = $request->getHeader($this->getSignatureHeaderName());

        if (empty($headers)) {
            return null;
        }

        return reset($headers);
    }

    protected function getGivenAppId(MessageInterface $request): ?string
    {
        $headers = $request->getHeader($this->getRemoteAppIdHeaderName());

        if (empty($headers)) {
            return null;
        }

        return reset($headers);
    }

    protected function findRemoteAppByRequest(MessageInterface $request): ?RemoteAppInterface
    {
        return $this->remoteAppResolver->findRemoteAppById($this->getGivenAppId($request));
    }

    protected function generateExpectedSignature(
        MessageInterface $request,
        RemoteAppInterface $remoteApp,
        string $algo
    ): string {
        $hash = hash_hmac($algo, (string) $request->getBody(), $remoteApp->getSecret());

        if (!$hash) {
            throw new AuthenticationException('Could not calculate the expected hash using algo '.$algo);
        }

        return $hash;
    }
}
