<?php

namespace Cblink\Service\Foundation\Traits;

use Cblink\Service\Foundation\Container;
use InvalidArgumentException;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;

trait InteractsWithCache
{

    /**
     * @var \Psr\SimpleCache\CacheInterface
     */
    protected $cache;

    /**
     * Get cache instance.
     *
     * @return \Psr\SimpleCache\CacheInterface
     *
     * @throws InvalidArgumentException
     */
    public function getCache()
    {
        if ($this->cache) {
            return $this->cache;
        }

        if (property_exists($this, 'app') && $this->app instanceof Container && isset($this->app['cache'])) {
            $this->setCache($this->app['cache']);

            // Fix PHPStan error
            assert($this->cache instanceof \Psr\SimpleCache\CacheInterface);

            return $this->cache;
        }

        return $this->cache = $this->createDefaultCache();
    }

    /**
     * Set cache instance.
     *
     * @param \Psr\SimpleCache\CacheInterface|\Psr\Cache\CacheItemPoolInterface $cache
     *
     * @return $this
     *
     * @throws InvalidArgumentException
     */
    public function setCache($cache)
    {
        if (empty(\array_intersect([CacheInterface::class, CacheItemInterface::class], \class_implements($cache)))) {
            throw new InvalidArgumentException(\sprintf('The cache instance must implements %s or %s interface.', CacheInterface::class, CacheItemInterface::class));
        }

        if ($cache instanceof CacheItemPoolInterface) {
            $cache = new Psr16Cache($cache);
        }

        $this->cache = $cache;

        return $this;
    }

    /**
     * @return \Psr\SimpleCache\CacheInterface
     */
    protected function createDefaultCache()
    {
        return new Psr16Cache(new FilesystemAdapter('idaas', 1500));
    }


}