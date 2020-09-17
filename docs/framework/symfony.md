# Using this package inside Symfony

## Symfony HTTP Client

```php
use Phpro\HttpTools\Client\Factory\ClientFactoryInterface;
use Psr\Http\Client\ClientInterface;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpClient\HttplugClient;

final class SymfonyClientFactory implements ClientFactoryInterface
{
    public function __invoke(array $options): ClientInterface
    {
        return new HttplugClient(
            new CurlHttpClient($options)
        );
    }
}
```

## Configuration

```yaml
```


## ...

