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
        class: Phpro\HttpTools\Plugin\AcceptLanguagePlugin
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
            - '@app.http.logger_formatter'
        tags:
            - { name: 'app.someclient.plugin', priority: 1000 }

    app.http.logger_formatter:
        stack:
            - Phpro\HttpTools\Formatter\RemoveSensitiveHeadersFormatter:
                $formatter: '@.inner'
                $sensitiveHeaders:
                    - 'X-Api-Key'
                    - 'X-Api-Secret'
            - Phpro\HttpTools\Formatter\RemoveSensitiveJsonKeysFormatter:
                $formatter: '@.inner'
                $sensitiveJsonKeys:
                    - password
                    - oldPassword
                    - refreshToken
            - '@app.http.logger_formatter.base'

    app.http.logger_formatter.base:
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
        factory: ['Phpro\HttpTools\Transport\Presets\JsonPreset', 'sync']
        arguments:
            - '@App\SomeClient'
            - '@Phpro\HttpTools\Uri\TemplatedUriBuilder'

    Phpro\HttpTools\Uri\TemplatedUriBuilder: ~

    #
    # Registering a single Request Handler
    #
    App\SomeClient\RequestHandler\ListSomething:
        arguments:
            - '@App\SomeClient\Transport'  

    #
    # Or register all Request Handlers at once
    #
    App\SomeClient\RequestHandler\:
        resource: '../../RequestHandler/*'
        bind:
            $transport: '@App\SomeClient\Transport'
```

If you are using the Symfony container inside functional tests.
You could make the VCR testing part of your dependency container by setting this up in `config/packages/test/services.yaml`.
Or any other env where you wish to use recorded responses by using the correct package path.
This way, you could also use the recordings as mocks for your frontend e.g.

```yaml
services:
    Http\Client\Plugin\Vcr\NamingStrategy\PathNamingStrategy:
        class: Http\Client\Plugin\Vcr\NamingStrategy\PathNamingStrategy

    Http\Client\Plugin\Vcr\Recorder\FilesystemRecorder:
        class: Http\Client\Plugin\Vcr\Recorder\FilesystemRecorder
        arguments: ['Tests/_fixtures']

    Http\Client\Plugin\Vcr\RecordPlugin:
        class: Http\Client\Plugin\Vcr\RecordPlugin
        arguments:
            - '@Http\Client\Plugin\Vcr\NamingStrategy\PathNamingStrategy'
            - '@Http\Client\Plugin\Vcr\Recorder\FilesystemRecorder'
        tags:
            - { name: 'app.someclient.plugin', priority: 1000 }
            - { name: 'app.otherclient.plugin', priority: 1000 }

    Http\Client\Plugin\Vcr\ReplayPlugin:
        class: Http\Client\Plugin\Vcr\ReplayPlugin
        arguments:
            - '@Http\Client\Plugin\Vcr\NamingStrategy\PathNamingStrategy'
            - '@Http\Client\Plugin\Vcr\Recorder\FilesystemRecorder'
            - false
        tags:
            - { name: 'app.someclient.plugin', priority: 1000 }
            - { name: 'app.otherclient.plugin', priority: 1000 }
```
