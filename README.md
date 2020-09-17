# Example Symfony configuration

## Setting up an HTTP client :

```yaml
services:
    App\SomeClient:
        factory: '@App\SomeClient\Factory'
        aguments:
            $options:
                base_uri: 'https://someclient.com'

    App\SomeClient\Factory:
        class: \Phpro\HttpTools\Client\Factory\ClientFactoryInterface
        stack:
            - Phpro\HttpTools\Client\Factory\PluginConfigurator
                arguments: ['@.inner', !tagged app.someclient.plugin] 
            - App\Http\SymfonyClientFactory
                
    App\SomeClient\Plugin\Logger:
        class: 'Http\Client\Common\Plugin\LoggerPlugin'
        arguments:
            - '@monolog.logger.someclient'
            - '@App\SomeClient\Plugin\Logger\Formatter'
        tags:
            - { name: 'app.someclient.plugin', priority: 1000 }

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

    App\SomeClient\Plugin\Logger\Formatter:
        class: Http\Message\Formatter
        stack:
            - Phpro\HttpTools\Formatter\RemoveSensitiveHeadersFormatter
                arguments: ['@.inner', [
                    'X-Api-Key',
                    'X-Api-Secret',
                ]]
            - Phpro\HttpTools\Formatter\RemoveSensitiveHeadersFormatter
                arguments: ['@.inner', [
                    'password',
                    'oldPassword',
                ]]
            - Phpro\HttpTools\Formatter\Factory\BasicFormatterFactory:
                arguments: ['%kernel.debug%']

    Phpro\HttpTools\Formatter\Factory\BasicFormatterFactory: ~

```


## Setting up a transport with request handlers:

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


## Testing in phpunit

```php
```