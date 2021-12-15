<?php

namespace Cblink\Service\Foundation\Traits;

use GuzzleHttp\HandlerStack;

trait HasHttpRequests
{
    /**
     * @var array
     */
    protected array $middlewares = [];

    /**
     * @var HandlerStack
     */
    protected $handlerStack;

    /**
     * Build a handler stack.
     *
     * @return HandlerStack
     */
    protected function getHandlerStack(): HandlerStack
    {
        if ($this->handlerStack) {
            return $this->handlerStack;
        }

        $this->handlerStack = HandlerStack::create($this->getGuzzleHandler());

        foreach ($this->middlewares as $name => $middleware) {
            $this->handlerStack->push($middleware, $name);
        }

        return $this->handlerStack;
    }

    /**
     * Get guzzle handler.
     *
     * @return callable
     */
    protected function getGuzzleHandler()
    {
        if (property_exists($this, 'app') && isset($this->app['guzzle_handler'])) {
            return is_string($handler = $this->app->get('guzzle_handler'))
                ? new $handler()
                : $handler;
        }

        return \GuzzleHttp\choose_handler();
    }

    /**
     * @param string $method
     * @param string $url
     * @param array $options
     * @return \Psr\Http\Message\ResponseInterface|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function request(string $method = 'POST', string $url = '', array $options = [])
    {
        $method = strtoupper($method);

        $options = array_merge($options, ['handler' => $this->getHandlerStack()]);

        return $this->app->client->request($method, $url, $options);
    }

    /**
     * Add a middleware.
     *
     * @param callable $middleware
     * @param string|null $name
     *
     * @return $this
     */
    protected function pushMiddleware(callable $middleware, string $name = null)
    {
        if (!is_null($name)) {
            $this->middlewares[$name] = $middleware;
        } else {
            array_push($this->middlewares, $middleware);
        }

        return $this;
    }
}