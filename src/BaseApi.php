<?php

namespace Cblink\Service\Foundation;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class BaseApi extends BaseRequestApi
{

    protected function registerHttpMiddlewares()
    {
        $this->pushMiddleware($this->accessTokenMiddleware(), 'access-token');
    }

    /**
     * Attache access token to request query.
     *
     * @return \Closure
     */
    protected function accessTokenMiddleware(): \Closure
    {
        return function (callable $handler) {
            return function (RequestInterface $request, array $options) use ($handler) {
                if ($this->accessToken) {
                    $request = $this->accessToken->applyToRequest($request, $options);
                }

                return $handler($request, $options);
            };
        };
    }
}