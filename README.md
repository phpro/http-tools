# HTTP-Tools

The goal of this package is to provide you some tools to set-up a consistent HTTP integration.
The HTTP client implementation you want to use is just a small implementation detail and doesn't matter.
However, here are some default guidelines:

## Setting up an HTTP client :

Setting up an HTTP client is done by using a factory.
The factory accepts a list of implementation specific plugins / middlewares.
Besides that, you can send options like a base_uri or default headers.

```yaml
services:
    App\SomeClient\HttpClient:
        factory: ['Phpro\HttpTools\Client\Factory\SymfonyClientFactory', 'create']
        # factory: ['Phpro\HttpTools\Client\Factory\GuzzleClientFactory', 'create']
        # factory: ['Phpro\HttpTools\Client\Factory\AutoDiscoveredClientFactory', 'create']
        arguments:
            $middlewares: !tagged app.someclient.plugin
            $options:
                base_uri: '%env(SOME_CLIENT_BASE_URI)%'
```

### Configuring the client through plugins

If you want to extend how an HTTP client works, we require you to use plugins!
You can use plugins for everything: logging, authentication, language specification, ...

Examples:

```yaml
services:        
    Phpro\HttpTools\Plugin\AcceptLanguagePlugin:
        arguments:
            - 'nl-BE'
        tags:
            - { name: 'app.someclient.plugin' }

    App\SomeClient\Plugin\Authentication\ServicePrincipal:
        arguments:
            - '%env(API_SECRET)%'
        tags:
            - { name: 'app.someclient.plugin' }
```

### Logging

This package contains the `php-http/logger-plugin`.
On top of that, we've added some decorators that help you strip out sensitive information from the logs.
You can switch from full to simple logging by specifying a debug parameter!

```yaml
    App\SomeClient\Plugin\Logger:
        class: 'Http\Client\Common\Plugin\LoggerPlugin'
        arguments:
            - '@monolog.logger.someclient'
            - '@App\SomeClient\Plugin\Logger\Formatter'
        tags:
            - { name: 'app.someclient.plugin', priority: 1000 }

    App\SomeClient\Plugin\Logger\Formatter:
        class: Http\Message\Formatter
        stack:
            - Phpro\HttpTools\Formatter\RemoveSensitiveHeadersFormatter
                $formattor: '@.inner'
                $sensitiveHeaders:
                    - 'X-Api-Key'
                    - 'X-Api-Secret'
                    - refreshToken
            - Phpro\HttpTools\Formatter\RemoveSensitiveJsonKeysFormatter
                arguments:
                    $formattor: '@.inner'
                    $sensitiveJsonKeyks:
                        - password
                        - oldPassword
                        - refreshToken
            - Phpro\HttpTools\Formatter\Factory\BasicFormatterFactory:
                factory: ['Phpro\HttpTools\Formatter\Factory\BasicFormatterFactory', 'create']
                class: Http\Message\Formatter
                arguments:
                    $debug: '%kernel.debug%'
                    $maxBodyLength: 1000
```

## Using the HTTP-Client

We don't want you to use the PSR-18 client directly! Instead, we suggest you to use a request handler principle.
So what does this architecture look like?

![Architecture](docs/assets/request-handlers.png)

* **Models**: Request / Response value objects that can be used as wrapper around raw arrays.
* **RequestHandler**: Transform a request into a response model by using a transport. You could add error handling in there as well.
* **Transport**: Transforms a Request model into a PSR-7 HTTP request and asks a response through the actual HTTP client. As an example, you could take the example of the: `JsonTransport`.
* **HTTP-Client**: Whichever PSR-18 HTTP client you want to use: guzzle, curl, symfony/http-client, ...


By using this architecture, we provide an easy to extend flow with models that replace cumbersome array structures.  
You might choose to create one big RequestHandler that can deal with multiple requests, but we suggest not to!

Example implementation:


```yaml
services:
    App\SomeClient\Transport:
        class: Phpro\HttpTools\Transport\TransportInterface
        stack: 
            - App\SomeClient\Transport\JsonErrorBodyTransport
                arguments: ['@.inner']
            - Phpro\HttpTools\Transport\Json\JsonTransport
                arguments:
                    - '@App\SomeClient'
                    - '@Http\Message\RequestFactory'
                    - '@Phpro\HttpTools\Uri\TemplatedUriBuilder'

    Phpro\HttpTools\Uri\TemplatedUriBuilder: ~

    App\SomeClient\RequestHandler\ListSomething:
        arguments:
            - '@App\SomeClient\Transport'
```

```php

use Phpro\HttpTools\Transport\TransportInterface;

class ListSomething
{
    public function __construct(
        private TransportInterface $transport
    ) {}

    public function __invoke(ListRequest $request): ListResponse
    {
        // You could validate the result first + throw exceptions based on invalid content
        // Tip : never trust APIs!
        // Try to gracefully fall back if possible and keep an eye on how the implementation needs to handle errors!

        return ListResponse::fromRawArray(
            ($this->transport)($request)
        );    
    }
}
```


## Testing HTTP clients

TODO

```php
```