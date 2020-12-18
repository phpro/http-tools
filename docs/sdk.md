# Creating a Software Development kit for API's

If your API is very straight forward, you might not want to create request handlers for every action.
You could for example create a more classic API with this package as well.

We provide some tools to compose straight-forward Rest HTTP resources.
Here is an example for a REST service:

```php
use Phpro\HttpTools\Sdk\HttpResource;
use Phpro\HttpTools\Sdk\Rest;
use Phpro\HttpTools\Request\Request;

/**
 * @template ResultType
 * @extends HttpResource<ResultType>
 */
final class UsersResouce extends HttpResource
{
    use Rest\CreateTrait;
    use Rest\FindTrait;
    use Rest\GetTrait;
    use Rest\UpdateTrait;
    use Rest\PatchTrait;
    use Rest\DeleteTrait;

    protected function path(): string
    {
        return '/users';
    }
    
    public function me()
    {
        $request = new Request('GET', '/user', [], null);
        return $this->transport()($request); 
    }
}
```

You could wrap multiple resources in an SDK client like this:

```php

use Phpro\HttpTools\Transport\TransportInterface;

/**
 * @template ResultType
 */
final class MyClient
{
    /**
     * @var UsersResouce<ResultType>
     * @psalm-readonly
     */
    public $users;

    /**
     * @param TransportInterface<array|null, ResultType> $transport
     */
    public function __construct(TransportInterface $transport)
    {
        $this->users = new UsersResouce($transport);
    }
}
```

Example usage:

```php
/** @var TransportInterface<array|null, UserModel> $transport */
/** @var MyClient<array> $sdk */
$sdk = new MyClient($transport);

var_dump($sdk->users->find('veewee'));
```

You could even swap the transport with one that supports a different output type and still keep type-safety!

> **Note**: If you are using the serializer transport, you might want to set the output types from within the Client.
> You might also want to create a separate function for delete and find.


# Built-in traits:

These are some very opinionated traits that can be used to build your SDK.
If you want to change something for your specific case, you can implement your own method or trait.

| Trait | Description |
| --- | --- |
| CreateTrait<ResponseType> | Can be used to create a new resource from an array |
| DeleteTrait<ResponseType> | Can be used to delete a resource from an identifier |
| FindTrait<ResponseType> | Can be used to find a resource with an identifier |
| GetTrait<ResponseType> | Can be used to list all resources |
| PatchTrait<ResponseType> | Can be used to patch a resource with an identifier from an array |
| UpdateTrait<ResponseType> | Can be used to update a resource with an identifier from an array |

