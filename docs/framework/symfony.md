# Using this package inside Symfony

## Example Configuration

```yaml
services:

    #
    # Configuring an HTTP client:
    #
    App\SomeClient\HttpClient:
        factory: ['Phpro\HttpTools\Client\Factory\SymfonyClientFactory', 'create']
        # factory: ['Phpro\HttpTools\Client\Factory\GuzzleClientFactory', 'create']
        # factory: ['Phpro\HttpTools\Client\Factory\AutoDiscoveredClientFactory', 'create']
        arguments:
            $middlewares: !tagged app.someclient.plugin
            $options:
                base_uri: '%env(SOME_CLIENT_BASE_URI)%'

    #
    # Configuring plugins:
    #
    App\SomeClient\Plugin\AcceptLanguagePlugin:
        class: Phpro\HttpTools\Plugin\AcceptLanguagePlugin:
        arguments:
            - 'nl-BE'
        tags:
            - { name: 'app.someclient.plugin' }

    App\SomeClient\Plugin\Authentication\ServicePrincipal:
        arguments:
            - '%env(API_SECRET)%'
        tags:
            - { name: 'app.someclient.plugin' }

    #
    # Logging:
    #
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
                $formatter: '@.inner'
                $sensitiveHeaders:
                    - 'X-Api-Key'
                    - 'X-Api-Secret'
                    - refreshToken
            - Phpro\HttpTools\Formatter\RemoveSensitiveJsonKeysFormatter
                arguments:
                    $formatter: '@.inner'
                    $sensitiveJsonKeys:
                        - password
                        - oldPassword
                        - refreshToken
            - Phpro\HttpTools\Formatter\Factory\BasicFormatterFactory:
                factory: ['Phpro\HttpTools\Formatter\Factory\BasicFormatterFactory', 'create']
                class: Http\Message\Formatter
                arguments:
                    $debug: '%kernel.debug%'
                    $maxBodyLength: 1000

    #
    # Setting up the transport
    #
    App\SomeClient\Transport:
        class: Phpro\HttpTools\Transport\TransportInterface
        stack: 
            - App\SomeClient\Transport\JsonErrorBodyTransport
                arguments: ['@.inner']
            - Phpro\HttpTools\Transport\Json\JsonTransport
                arguments:
                    - '@App\SomeClient'
                    - '@Phpro\HttpTools\Uri\TemplatedUriBuilder'
                    - '@Http\Message\RequestFactory'
                    - '@Http\Message\StreamFactory'

    Phpro\HttpTools\Uri\TemplatedUriBuilder: ~


    #
    # Registering a Request Handler
    #
    App\SomeClient\RequestHandler\ListSomething:
        arguments:
            - '@App\SomeClient\Transport'  
```
