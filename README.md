<a href="https://bas.dev">
    <img src="https://bmcdn.nl/assets/branding/logo.svg" alt="Bas Milius" height="48" />
</a>

---

# Raxos OpenAPI

Generate OpenAPI 3.1.1 JSON or YAML from Raxos controllers, PHP types and documentation attributes.

[Documentation](https://raxos.dev/openapi/) | [Packagist](https://packagist.org/packages/raxos/openapi) | [Raxos](https://github.com/basmilius/raxos)

- Endpoint, response, request-model and parameter documentation.
- Reusable schemas, recursive DTO references and JSON Schema null unions.
- Query and middleware parameter discovery and search-filter descriptions.

## Installation

Requires PHP 8.5 or later. Enable the `fileinfo`, `json`, `simplexml` PHP extensions. Composer checks the remaining package and extension dependencies declared in [composer.json](composer.json).

```sh
composer require "raxos/openapi:^3.3"
```

## Usage

```php
<?php
declare(strict_types=1);

use Raxos\Container\Container;
use Raxos\Http\HttpResponseCode;
use Raxos\Http\Response\JsonHttpResponse;
use Raxos\OpenAPI\Attribute\Endpoint;
use Raxos\OpenAPI\Attribute\Response;
use Raxos\OpenAPI\Definition\Components;
use Raxos\OpenAPI\Definition\Info;
use Raxos\OpenAPI\OpenAPI;
use Raxos\OpenAPI\RouterBuilder;
use Raxos\Router\Attribute\Controller;
use Raxos\Router\Attribute\Get;
use Raxos\Router\Router;

require __DIR__ . '/vendor/autoload.php';

#[Controller('/health')]
final readonly class HealthController
{
    #[Get('/')]
    #[Endpoint(summary: 'Read the service status.')]
    #[Response(HttpResponseCode::OK, description: 'The service is available.')]
    public function index(): JsonHttpResponse
    {
        return new JsonHttpResponse(['status' => 'ok']);
    }
}

$router = Router::createFromControllers(new Container(), [HealthController::class]);
$builder = new RouterBuilder($router);
$builder->build();

$document = new OpenAPI(
    info: new Info(title: 'Example API', version: '1.0.0'),
    paths: $builder->paths->toArray(),
    components: new Components(
        responses: $builder->responses->toArray(),
        schemas: $builder->schemas->toArray()
    )
);

echo $document->getJSON();
```

Only methods marked with `#[Endpoint]` are documented. Use response `model` arguments and schema attributes to describe payloads; the status-only response above has no body schema. `getYAML()` renders the same document as YAML. Regenerate client contracts after adopting the 3.2 JSON Schema changes.

## Documentation

- [Documenting endpoints](https://raxos.dev/openapi/documenting-endpoints)
- [Documenting schemas](https://raxos.dev/openapi/documenting-schemas)
- [Generating a specification](https://raxos.dev/openapi/generating-a-spec)
- [Document metadata and security](https://raxos.dev/openapi/spec-metadata)

## Testing

Run this library's Pest suite from the Raxos workspace:

```sh
git clone --recurse-submodules https://github.com/basmilius/raxos.git
cd raxos
composer install
vendor/bin/pest --testsuite=openapi
```

See [Testing Raxos](https://github.com/basmilius/raxos/blob/main/TESTING.md) for PHP extensions, integration services and coverage commands. The library's [Tests workflow](.github/workflows/tests.yml) also runs in GitHub Actions.

## License

[MIT](LICENSE). Copyright (c) 2017 - present Bas Milius.

See [input schemas and validation](https://raxos.dev/openapi/input-schemas) for the optional APIs and their lifetime or transport guarantees.
