<?php

namespace Tests;

use GuzzleHttp\Psr7\Request;
use Kronthto\Psr7RequestSignature\AppResolver\AppResolver;
use Kronthto\Psr7RequestSignature\AuthenticationException;
use Kronthto\Psr7RequestSignature\RemoteAppInterface;
use Kronthto\Psr7RequestSignature\Verifier;
use PHPUnit\Framework\TestCase;

class RemoteAppValidatorTest extends TestCase
{
    /** @var Verifier */
    protected $verifier;

    /** @var RemoteAppInterface */
    protected $remoteAppOne;

    public function setUp()
    {
        parent::setUp();

        $remoteApp = \Mockery::mock(RemoteAppInterface::class);
        $remoteApp->shouldReceive('getSecret')->andReturn('theriversofbabylon');
        $this->remoteAppOne = $remoteApp;

        $appResolver = \Mockery::mock(AppResolver::class);
        $appResolver->shouldReceive('findRemoteAppById')->with(2)->andReturn(null);
        $appResolver->shouldReceive('findRemoteAppById')->with(1)->andReturn($remoteApp);

        $this->verifier = new Verifier($appResolver);
    }

    public function testValidateRemoteAppRequestUnknownClient()
    {
        $request = new Request('POST', '/foo', [
            Verifier::DEFAULT_ID_HEADER => 2,
        ], '{"foo": 1}');

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessageRegExp('/App-ID not found/');

        $this->verifier->validateRemoteAppRequest($request);
    }

    public function testValidateRemoteAppRequestWrongSignature()
    {
        $request = new Request('POST', '/foo', [
            Verifier::DEFAULT_ID_HEADER => 1,
            Verifier::DEFAULT_SIG_HEADER => 'sha1=f0b888107dde2dcc91628cf6fcf669be8e9c3861',
        ], '{"foo": 1}');

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessageRegExp('/signature invalid/');

        $this->verifier->validateRemoteAppRequest($request);
    }

    public function testValidateRemoteAppRequestHappyCase()
    {
        $request = new Request('POST', '/foo', [
            Verifier::DEFAULT_ID_HEADER => 1,
            Verifier::DEFAULT_SIG_HEADER => 'sha1=00b888107dde2dcc91628cf6fcf669be8e9c3861',
        ], '{"foo": 1}');

        $result = $this->verifier->validateRemoteAppRequest($request);

        $this->assertInstanceOf(RemoteAppInterface::class, $result);
        $this->assertSame($this->remoteAppOne, $result);
    }

    public function testValidateRemoteAppRequestHappyCaseSha256()
    {
        $request = new Request('POST', '/foo', [
            Verifier::DEFAULT_ID_HEADER => 1,
            Verifier::DEFAULT_SIG_HEADER => 'sha256=f5fd538a138e8eecd52abb9a3297e195cb871f0ab1222c9f10b9f4f7e6e6c262',
        ], '{"foo": 1}');

        $result = $this->verifier->validateRemoteAppRequest($request);

        $this->assertInstanceOf(RemoteAppInterface::class, $result);
        $this->assertSame($this->remoteAppOne, $result);
    }

    // TODO: Add tests for overriding options & non-existent headers / error cases
}
