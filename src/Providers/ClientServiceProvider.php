<?php

namespace Cblink\Service\Foundation\Providers;

use Hyperf\Guzzle\ClientFactory;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ClientServiceProvider implements ServiceProviderInterface
{

    public function register(Container $pimple)
    {
        $pimple['client'] = function($pimple){
            $client = new ClientFactory($pimple);
            return $client->create($pimple['config']->get('guzzle', []));
        };
    }
}