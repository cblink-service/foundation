<?php

namespace Cblink\Service\Foundation;

use Cblink\Service\Foundation\Traits\HasHttpRequests;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class BaseApi
{
    use HasHttpRequests {
        request as performRequest;
    }

    /**
     * @var Container
     */
    public $app;

    /**
     * @var AccessToken|null
     */
    public $accessToken = null;

    public function __construct(Container $container, $accessToken = null)
    {
        $this->app = $container;

        $this->accessToken = $accessToken ?? $this->app->has('access_token') ? $this->app['access_token'] : null;
    }

    /**
     * GET request.
     *
     * @param $url
     * @param array $query
     * @return \Psr\Http\Message\ResponseInterface|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function httpGet($url, array $query = [])
    {
        return $this->request('GET', $url, ['query' => $query]);
    }

    /**
     * POST request.
     *
     * @param string $url
     * @param array $data
     * @param array $query
     * @return \Psr\Http\Message\ResponseInterface|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function httpPost(string $url, array $data = [], array $query = [])
    {
        return $this->request('POST', $url, ['query' => $query, 'json' => $data]);
    }

    /**
     * PUT request.
     *
     * @param string $url
     * @param array $data
     * @param array $query
     * @return \Psr\Http\Message\ResponseInterface|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function httpPut(string $url, array $data = [], array $query = [])
    {
        return $this->request('PUT', $url, ['query' => $query, 'json' => $data]);
    }

    /**
     * DELETE request.
     *
     * @param $url
     * @param array $query
     * @return \Psr\Http\Message\ResponseInterface|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function httpDelete($url, array $query = [])
    {
        return $this->request('DELETE', $url, ['query' => $query]);
    }

    /**
     * ??????
     *
     * @param string $method
     * @param string $url
     * @param array $options
     * @param bool $returnRaw
     * @return \Psr\Http\Message\ResponseInterface|array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function request(string $method = 'POST', string $url = '', array $options = [], $returnRaw = false)
    {
        if (empty($this->middlewares)) {
            $this->registerHttpMiddlewares();
        }

        $response = $this->performRequest($method, $this->getRequestUrl($url), $options);

        return $returnRaw ? $response : $this->castResponseToType($response);
    }

    /**
     * @param ResponseInterface $response
     * @return array
     */
    protected function castResponseToType(ResponseInterface $response): array
    {
        $response->getBody()->rewind();
        $contents = $response->getBody()->getContents();
        $response->getBody()->rewind();

        $array = json_decode($contents, true, 512, JSON_BIGINT_AS_STRING);

        if (JSON_ERROR_NONE === json_last_error()) {
            return (array) $array;
        }

        return [];
    }

    /**
     * @param $url
     * @return string
     */
    protected function getRequestUrl($url): string
    {
        $baseUrl = '';

        if(!empty($this->app['config']->get('base_url'))) {
            $baseUrl = $this->app['config']->get('base_url');
        }

        return rtrim($baseUrl, '/') . '/' . ltrim($url, '/');
    }

    /**
     *
     */
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