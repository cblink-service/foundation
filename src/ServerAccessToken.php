<?php

namespace Cblink\Service\Foundation;

class ServerAccessToken extends AccessToken
{
    public string $endpointToGetToken = '/api/server/access-token';

    public function getCredentials() :array
    {
        return [
            'appid' => $this->app['config']->get('appid'),
            'secret' => md5($this->app['config']->get('secret')),
        ];
    }
}