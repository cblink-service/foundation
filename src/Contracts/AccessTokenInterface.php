<?php

namespace Cblink\Service\Foundation\Contracts;

use Psr\Http\Message\RequestInterface;

interface AccessTokenInterface
{
    /**
     * @param RequestInterface $request
     * @param array $requestOptions
     * @return RequestInterface
     */
    public function applyToRequest(RequestInterface $request, array $requestOptions = []): RequestInterface;
}