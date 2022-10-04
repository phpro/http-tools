<?php

declare(strict_types=1);

require dirname(__DIR__).'/vendor/autoload.php';

use Http\Client\Common\Plugin\AuthenticationPlugin;
use Http\Client\Common\Plugin\BaseUriPlugin;
use Http\Message\Authentication\BasicAuth;
use Nyholm\Psr7\Uri;
use Phpro\HttpTools\Client\Factory\AutoDiscoveredClientFactory;
use Phpro\HttpTools\Sdk\HttpResource;
use Phpro\HttpTools\Sdk\Rest\CreateTrait;
use Phpro\HttpTools\Sdk\Rest\DeleteTrait;
use Phpro\HttpTools\Sdk\Rest\FindTrait;
use Phpro\HttpTools\Sdk\Rest\GetTrait;
use Phpro\HttpTools\Sdk\Rest\PatchTrait;
use Phpro\HttpTools\Sdk\Rest\UpdateTrait;
use Phpro\HttpTools\Transport\Presets\JsonPreset;
use Phpro\HttpTools\Transport\TransportInterface;
use Phpro\HttpTools\Uri\TemplatedUriBuilder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

$normalizers = [new ObjectNormalizer(), new ArrayDenormalizer()];
$encoders = [new JsonEncoder()];
$serializer = new Serializer($normalizers, $encoders);

$client = AutoDiscoveredClientFactory::create([
    new BaseUriPlugin(new Uri('https://api.github.com')),
    new AuthenticationPlugin(
        new BasicAuth('user', 'pass')
    ),
]);

$transport = JsonPreset::sync($client, new TemplatedUriBuilder());

/**
 * @template ResultType
 *
 * @extends HttpResource<ResultType>
 */
final class UsersResource extends HttpResource
{
    use CreateTrait;
    use DeleteTrait;
    use FindTrait;
    use GetTrait;
    use PatchTrait;
    use UpdateTrait;

    protected function path(): string
    {
        return '/users';
    }
}

/**
 * @template ResultType
 */
final class Sdk
{
    /**
     * @var UsersResource<ResultType>
     *
     * @psalm-readonly
     */
    public UsersResource $users;

    /**
     * @param TransportInterface<array|null, ResultType> $transport
     */
    public function __construct(TransportInterface $transport)
    {
        $this->users = new UsersResource($transport);
    }
}

/** @var Sdk<array> $sdk */
$sdk = new Sdk($transport);

/** @psalm-suppress ForbiddenCode */
var_dump($sdk->users->find('veewee'));
