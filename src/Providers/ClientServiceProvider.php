<?php

namespace Cblink\Service\Foundation\Providers;

use GuzzleHttp\Client;
use Hyperf\Guzzle\ClientFactory;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ClientServiceProvider implements ServiceProviderInterface
{

    public function register(Container $pimple)
    {
        $pimple['client'] = function($pimple){

            $config = $pimple['config']->get('guzzle', []);

            return class_exists(\Swoole\Coroutine::class) ?
                (new ClientFactory($pimple))->create($config) :
                new Client($config);
        };
    }
}