<?php

namespace Cblink\Service\Foundation\Providers;

use GuzzleHttp\Client;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ClientServiceProvider implements ServiceProviderInterface
{

    public function register(Container $pimple)
    {
        $pimple['client'] = function($pimple){
            return new Client($pimple['config']->get('guzzle', []));
        };
    }
}