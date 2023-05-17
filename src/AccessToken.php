<?php

namespace Cblink\Service\Foundation;

use Cblink\Service\Foundation\Contracts\AccessTokenInterface;
use Cblink\Service\Foundation\Traits\HasHttpRequests;
use Cblink\Service\Foundation\Traits\InteractsWithCache;
use Hyperf\Utils\Arr;
use Psr\Http\Message\RequestInterface;
use RuntimeException;

class AccessToken implements AccessTokenInterface
{
    use InteractsWithCache, HasHttpRequests;

    /**
     * @var Container
     */
    protected $app;

    protected string $tokenKey = 'access_token';

    protected string $lifeKey = 'expire';

    protected string $endpointToGetToken = 'api/access-token';
    protected string $authType = 'Bearer';

    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    /**
     * 获取token
     *
     * @return array|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getToken(RequestInterface $request, array $requestOptions = [])
    {
        $cacheKey = $this->getCacheKey();
        $cache = $this->getCache();

        if ($cache->has($cacheKey) && !empty($result = $cache->get($cacheKey))) {
            return Arr::get($result, $this->tokenKey);
        }

        /** @var array $token */
        $token = $this->requestToken();

        $lifeTime = (int) Arr::get($token, $this->lifeKey, 7200);
        // 缩短过期时间
        $this->setToken($accessToken = Arr::get($token, $this->tokenKey), ($lifeTime - 10));

        return $accessToken;
    }

    /**
     * 设置Token
     *
     * @param $token
     * @param int $lifetime
     * @return $this
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function setToken($token, int $lifetime = 7200): AccessToken
    {
        $this->getCache()->set($this->getCacheKey(), [
            $this->tokenKey => $token,
            'expires' => $lifetime,
        ], $lifetime);

        if (!$this->getCache()->has($this->getCacheKey())) {
            throw new RuntimeException('Failed to cache access token.');
        }

        return $this;
    }

    /**
     * 请求access token
     *
     * @return array|\Psr\Http\Message\ResponseInterface|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function requestToken()
    {
        $response = $this->request('POST', $this->getRequestUrl($this->endpointToGetToken), ['json' => $this->getCredentials()]);

        $response = $response->getBody()->getContents();

        $body = json_decode($response, true);

        if (json_last_error()) {
            throw new HttpClientException(sprintf('Request access_token fail: %s', $response));
        }

        if (!isset($body['err_code']) || $body['err_code'] > 0) {
            throw new HttpClientException('Request access_token fail: '. json_encode($body, JSON_UNESCAPED_UNICODE) .', url: '. $this->getRequestUrl($this->endpointToGetToken));
        }

        return Arr::get($body, 'data', []);
    }

    /**
     * @param RequestInterface $request
     * @param array $requestOptions
     * @return RequestInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function applyToRequest(RequestInterface $request, array $requestOptions = []): RequestInterface
    {
        return $request->withHeader('Authorization', trim(sprintf('%s %s', $this->authType, $this->getToken($request, $requestOptions))));
    }

    /**
     * @return string
     */
    protected function getCacheKey(): string
    {
        return 'access-token-' . md5(json_encode($this->getCredentials(), JSON_UNESCAPED_UNICODE));
    }

    /**
     * Credential for get token.
     *
     * @return array
     */
    protected function getCredentials(): array
    {
        return [
            'appid' => $this->app['config']->get('appid'),
            'secret' => $this->app['config']->get('secret'),
        ];
    }
}