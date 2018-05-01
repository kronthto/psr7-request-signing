<?php

namespace Tests;

use GuzzleHttp\Psr7\Request;
use Kronthto\Psr7RequestSignature\Signer;
use PHPUnit\Framework\TestCase;

class SignerTest extends TestCase
{
    public function testCreateSignatureHeaderForRequest()
    {
        $request = new Request('POST', '/foo', [], '{"foo": 1}');

        $this->assertSame(
            'md5=c8433954fdca58ccf25822cc21fbddd9',
            Signer::createSignatureHeaderForRequest($request, 'theriversofbabylon', 'md5')
        );
    }

    public function testThrowsForInvalidAlgos()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageRegExp('/not calculate hash using algo rott12/');

        Signer::createHmacSignatureForPayload('data', 'secrett', 'rott12');
    }
}
