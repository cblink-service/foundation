<?php

namespace Cblink\Service\Foundation;

use Cblink\Service\Foundation\Traits\HasHttpRequests;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class BaseRequestApi
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
     * 请求
     *
     * @param string $method
     * @param string $url
     * @param array $options
     * @param bool $returnRaw
     * @return \Psr\Http\Message\ResponseInterface|array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function request(string $method = 'POST', string $url = '', array $options = [], bool $returnRaw = false)
    {
        if (empty($this->middlewares) && method_exists($this, 'registerHttpMiddlewares')) {
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
}