<?php

namespace Cblink\Service\Foundation\Providers;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class BaseServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        $pimple['base'] = function($pimple){
            return new BaseClient($pimple);
        };
    }
}