<?php

namespace Kronthto\Psr7RequestSignature\AppResolver;

use Kronthto\Psr7RequestSignature\RemoteAppInterface;

class ArrayResolver implements AppResolver
{
    /** @var array */
    protected $table;

    /**
     * @param array $apps of structure [['id' => 'a1', 'secret' => 'aaffzz6#']]
     */
    public function __construct(array $apps)
    {
        $this->table = static::parseLookupTable($apps);
    }

    protected static function parseLookupTable(array $apps): array
    {
        $table = [];
        foreach ($apps as $app) {
            $appObject = new RemoteAppConcrete();
            $appObject->secret = $app['secret'];
            $table[$app['id']] = $appObject;
        }

        return $table;
    }

    public function findRemoteAppById($id): ?RemoteAppInterface
    {
        return $this->table[$id] ?? null;
    }
}

class RemoteAppConcrete implements RemoteAppInterface
{
    /** @var string */
    public $secret;

    public function getSecret(): string
    {
        return $this->secret;
    }
}
