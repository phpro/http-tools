# Using this package inside Magento2

## Configuration via di.xml
1. Define and configure your custom plugins/middlewares or implement some of [HTTPlug](http://docs.php-http.org/en/latest/plugins/)
2. Use `Phpro\HttpTools\Client\Factory\LazyClientLoader` to configure your preferred HTTP client (`AutoDiscoveredClientFactory`, `GuzzleClientFactory`, `SymfonyClientFactory`, ...) and define options such as base_uri, default headers, ...
3. Always create your own _Transport_ class as decorator. Magento don't know the concept of factories or stack in its DI component. Inside your custom _Transport_ you can use the built-in transports like e.g. `JsonPreset`
4. Last but not least, create and configure your _Request Handler(s)_ to transform _Request Value Object(s)_ to _Response Value Object(s)_

Example etc/di.xml file
```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd">

<!--    <type name="Mage\OpenWeather\Http\Plugin\DummyPlugin">-->
<!--        <arguments>-->
<!--            <argument name="headerValue" xsi:type="string">some-value-for-header</argument>-->
<!--        </arguments>-->
<!--    </type>-->

    <type name="Phpro\HttpTools\Plugin\AcceptLanguagePlugin">
        <arguments>
            <argument name="acceptLanguage" xsi:type="string">nl-BE</argument>
        </arguments>
    </type>

    <type name="Phpro\HttpTools\Client\Factory\LazyClientLoader">
        <arguments>
            <argument name="factory" xsi:type="string">\Phpro\HttpTools\Client\Factory\SymfonyClientFactory</argument>
            <argument name="middlewares" xsi:type="array">
                <!-- <item name="loggerPlugin" xsi:type="object">SomeLoggerPlugin</item>  -->
                <!-- <item name="dummyPlugin" xsi:type="object">Mage\OpenWeather\Http\Plugin\DummyPlugin</item> -->
                <item name="acceptLanguagePlugin" xsi:type="object">Phpro\HttpTools\Plugin\AcceptLanguagePlugin</item>
            </argument>
            <argument name="options" xsi:type="array">
                <item name="base_uri" xsi:type="string">http://api.openweathermap.org</item>
            </argument>
        </arguments>
    </type>

    <!-- In case you want to define uri variables, you can enable this type and add it as explicit dependency to your transport -->
    <!-- <type name="Phpro\HttpTools\Uri\TemplatedUriBuilder">-->
    <!--     <arguments>-->
    <!--            <argument name="defaultVariables" xsi:type="array">-->
    <!--                <item name="foo" xsi:type="string">bar</item>-->
    <!--            </argument>-->
    <!--        </arguments>-->
    <!-- </type>-->

    <type name="Mage\OpenWeather\Http\Transport">
        <arguments>
            <argument name="clientLoader" xsi:type="object">Phpro\HttpTools\Client\Factory\LazyClientLoader</argument>
            <!-- Optional arguments, Magento resolves this automatically. Also check type Phpro\HttpTools\Uri\TemplatedUriBuilder above. -->
            <!--<argument name="uriBuilder" xsi:type="object">Phpro\HttpTools\Uri\TemplatedUriBuilder</argument>-->
        </arguments>
    </type>

    <type name="Mage\OpenWeather\Http\RequestHandler\Weather">
        <arguments>
            <argument name="transport" xsi:type="object">Mage\OpenWeather\Http\Transport</argument>
        </arguments>
    </type>
</config>
```

## Example Transport

```php
<?php
declare(strict_types=1);

namespace Mage\OpenWeather\Http;

use Phpro\HttpTools\Client\Factory\LazyClientLoader;
use Phpro\HttpTools\Request\RequestInterface;
use Phpro\HttpTools\Transport\Presets\JsonPreset;
use Phpro\HttpTools\Transport\TransportInterface;
use Phpro\HttpTools\Uri\TemplatedUriBuilder;

class Transport implements TransportInterface
{
    /**
     * @var TransportInterface
     */
    private $jsonTransport;

    public function __construct(
        LazyClientLoader $clientLoader,
        TemplatedUriBuilder $uriBuilder
    ) {
        $this->jsonTransport = JsonPreset::sync(
            $clientLoader->load(),
            $uriBuilder
        );
    }

    public function __invoke(RequestInterface $request): array
    {
        return ($this->jsonTransport)($request);
    }
}
```

## Example Request Handler
```php
<?php
declare(strict_types=1);

namespace Mage\OpenWeather\Http\RequestHandler;

use Mage\OpenWeather\Http\Request\WeatherRequest;
use Mage\OpenWeather\Http\Response\WeatherResponse;
use Phpro\HttpTools\Transport\TransportInterface;

class Weather
{
    /**
     * @var TransportInterface
     */
    private $transport;

    public function __construct(TransportInterface $transport)
    {
        $this->transport = $transport;
    }

    public function __invoke(WeatherRequest $weatherRequest): WeatherResponse
    {
        # You could add data conversion, error handling, ... here. 
        return WeatherResponse::fromRawArray(
            ($this->transport)($weatherRequest)
        );
    }
}
```

## Example Request Value Object
```php
<?php
declare(strict_types=1);

namespace Mage\OpenWeather\Http\Request;

use Phpro\HttpTools\Request\RequestInterface;

class WeatherRequest implements RequestInterface
{

    public function method(): string
    {
        return 'GET';
    }

    public function uri(): string
    {
        return 'data/2.5/weather?q={location}&APPID={appid}';
    }

    public function uriParameters(): array
    {
        return [
            'location' => 'Antwerp,be',
            'appid' => '...'
        ];
    }

    public function body(): array
    {
        return [];
    }
}
```

## Example Response Value Object
```php
<?php
declare(strict_types=1);

namespace Mage\OpenWeather\Http\Response;

class WeatherResponse
{
    private $type;

    private $description;

    public function __construct(string $type, string $description)
    {
        $this->type = trim($type);
        $this->description = trim($description);
    }

    public static function fromRawArray(array $data): self
    {
        if (!isset($data['weather']) || !is_array($data['weather']) || !isset($data['weather'][0]['main'])) {
            throw new \Exception('Invalid data provided');
        }

        return new self(
            $data['weather'][0]['main'],
            $data['weather'][0]['description']
        );
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}
```
