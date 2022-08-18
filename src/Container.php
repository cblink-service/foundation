<?php

namespace Cblink\Service\Foundation;

use Closure;
use Illuminate\Support\Collection;
use Pimple\Container as Pimple;
use Psr\Container\ContainerInterface;

/**
 * @property Collection $config
 * @property \GuzzleHttp\Client $client
 * @property Providers\BaseClient $base
 */
class Container extends Pimple implements ContainerInterface
{
    /**
     * @var array
     */
    protected array $providers = [];

    public function __construct(array $config = [])
    {
        $this->registerBase($config);
        parent::__construct([]);
        $this->registerProviders();
    }

    public function registerBase($config)
    {
        $this->offsetSet('config', new Collection($config));
    }

    /**
     *
     */
    public function registerProviders()
    {
        foreach (array_merge($this->baseProviders(), $this->providers) as $provider) {
            $this->register(new $provider);
        }
    }

    /**
     * @return string[]
     */
    public function baseProviders(): array
    {
        return [
            Providers\ClientServiceProvider::class,
            Providers\BaseServiceProvider::class,
        ];
    }

    /**
     * @param $id
     * @param Closure $closure
     */
    public function rebind($id, Closure  $closure)
    {
        $this->offsetSet($id, $closure);
    }

    /**
     * @param string $id
     * @return mixed
     */
    public function get(string $id)
    {
        return $this->offsetGet($id);
    }

    /**
     * @param string $id
     * @return bool
     */
    public function has(string $id): bool
    {
        return $this->offsetExists($id);
    }

    public function __get($name)
    {
        return $this->offsetGet($name);
    }
}