<h1 align="center"> foundation-sdk </h1>

<p align="center"> .</p>


## 安装

```shell
$ composer require cblink-service/foundation -vvv
```

## 使用

### 容器
Container 是整个项目的核心，继承了 Pimple\Container，也就是一个容器。你所使用的 $foundation->order $foundation->config $foundation->user 等等都是因为这个是一个容器。中含有一个 $providers 服务提供者的数组属性。

### 服务提供者
容器的最佳表现。接上面所说的，你可以在 Container 中注册多个服务提供者，
新建的服务提供者需要实现 Pimple\ServiceProviderInterface，并补充完 register 方法，其实就是 new 一个类在 容器中，[参考这里](https://github.com/cblink-service/foundation/blob/master/src/Providers/ClientServiceProvider.php)

### 初始化
```php

// 继承基类
class App extends \Cblink\Service\Foundation\Container {
    protected array $providers = [
        // 自定义服务提供者
        \Cblink\Service\Foundation\Providers\AccessTokenServiceProvider::class,
    ];
}

$app = new App([
    // 接口的基础请求地址
    'base_url' => '',
    // 使用 guzzle 时所需要的默认配置
    'guzzle' => [
        'timeout' => 5.0,
        'verify' => false,
    ],
])
```

### API
凡是写 SDK 都需要去新建一个 Api 的类，这个类需要去继承 `Cblink\Service\Foundation\BaseApi` 或 `Cblink\Service\Foundation\BaseRequestApi`。 

`BaseApi`类中默认包含了实现认证的中间件，需要先将`access_token`的服务提供者引入容器中，完成在 header 中增加 Authorization已达到完成认证的目的。[参考这里](https://github.com/cblink-service/foundation/blob/master/src/AccessToken.php)
如SDK中提供的认证机制不满足使用，可以自行实现 `access_token` 类，只需要将类实现 `Cblink\Service\Foundation\Contracts\AccessTokenInterface` 中的方法即可

`BaseRequestApi` 通常用于无需认证的接口。

```php
# 在Api类中获取配置
class Api extends \Cblink\Service\Foundation\BaseRequestApi {

    public function getOrderLists()
    {
        // 所有的接口请求，默认为json格式返回，返回值将会转换成数组返回
       /* @var array $response */
       $response = $this->httpGet('/url', ['query' => []]); 
       
       // post请求
       $response = $this->httpPost('/url', ['data']); 
       
       // put 请求
       $this->httpPut();
       
       // delete 请求
       $this->httpDelete('/url', ['query']) 
    }
    
    
    // 可以再Api中声明名为 getBaseUrl 的方法，此方法声明后将会覆盖 config 中的 base_url
    public function getBaseUrl()
    {
        return 'http://www.cblink.net';
    }
}
```

## Contributing

You can contribute in one of three ways:

1. File bug reports using the [issue tracker](https://github.com/cblink-service/idaas-sdk/issues).
2. Answer questions or fix bugs on the [issue tracker](https://github.com/cblink-service/idaas-sdk/issues).
3. Contribute new features or update the wiki.

_The code contribution process is not very formal. You just need to make sure that you follow the PSR-0, PSR-1, and PSR-2 coding guidelines. Any new code contributions must be accompanied by unit tests where applicable._

## License

MIT