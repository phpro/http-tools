# Dealing with files

## Download file

For downloading files, you can use the `BinaryDecoder` - which has its own `BinaryDownloadPreset`:

```php
use Phpro\HttpTools\Encoding\Binary\BinaryFile;
use Phpro\HttpTools\Request\Request;
use Phpro\HttpTools\Transport\Presets\BinaryDownloadPreset;

$transport = BinaryDownloadPreset::create($client, $uriBuilder);
$binaryFile = $transport(
    new Request('GET', '/some/file', [], null),
);

assert($binaryFile instanceof BinaryFile);
var_dump(
    $binaryFile->stream(),
    $binaryFile->fileSizeInBytes(),
    $binaryFile->hash(),
    $binaryFile->extension(),
    $binaryFile->mimeType(),
    $binaryFile->fileName(),
);
```

This decoder will try to parse some information from the Response headers and stream:

* The stream body can be used to tell the size in bytes of the response or to calculate an MD5 (or other algorithm's) hash.
* `Content-Type`: could be used to guess the mime-type
* `Content-Disposistion`: could result in guessing the filename, extension and/or mime-type.
* `Content-Length`: could be used to guess the size in bytes of the response.

The decoder is highly configurable, meaning you can alter the way it guesses specific properties or opt-out on intenser logic like calculating a md5 hash.


## Upload file(s)

If you have to upload files to an API server, you could use the `MultiPartEncoder` which uses `symfony/mime` internally.
An example setup could look like this:

```php
use Phpro\HttpTools\Encoding\Json\JsonDecoder;
use Phpro\HttpTools\Encoding\Mime\MultiPartEncoder;
use Phpro\HttpTools\Transport\EncodedTransportFactory;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Phpro\HttpTools\Request\Request;

$transport = EncodedTransportFactory::create(
    $client,
    $uriBuilder,
    MultiPartEncoder::createWithAutodiscoveredPsrFactories(),
    JsonDecoder::createWithAutodiscoveredPsrFactories()
);

$jsonData = $transport(
    new Request('POST', '/some/file', [], new FormDataPart([
        'name' => 'Jos bos',
        'profile-pic' => DataPart::fromPath('/my-profile-pic.jpg')
    ])),
);
```

**Note:** If you wish not to use `symfony/mime` for uploading files, you could use `guzzle/psr7`'s `MultipartStream` with the existing `StreamEncoder` option:

```php
use GuzzleHttp\Psr7\MultipartStream;
use Phpro\HttpTools\Encoding\Json\JsonDecoder;
use Phpro\HttpTools\Encoding\Stream\StreamEncoder;
use Phpro\HttpTools\Transport\EncodedTransportFactory;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Phpro\HttpTools\Request\Request;

$transport = EncodedTransportFactory::create(
    $client,
    $uriBuilder,
    StreamEncoder::createWithAutodiscoveredPsrFactories(),
    JsonDecoder::createWithAutodiscoveredPsrFactories()
);

$multiPartStream = new MultipartStream($parts)
$jsonData = $transport(
    new Request('POST', '/some/file', [], $multiPartStream)),
);
```
