<?php

namespace Tests;

use Kronthto\Psr7RequestSignature\AppResolver\ArrayResolver;
use Kronthto\Psr7RequestSignature\RemoteAppInterface;
use PHPUnit\Framework\TestCase;

class ArrayResolverTest extends TestCase
{
    public function testArrayResolver()
    {
        $resolver = new ArrayResolver([
            ['id' => 5, 'secret' => 'gg'],
        ]);

        $this->assertNull($resolver->findRemoteAppById(9));
        $app5 = $resolver->findRemoteAppById('5');
        $this->assertInstanceOf(RemoteAppInterface::class, $app5);
        $this->assertEquals('gg', $app5->getSecret());
    }
}
