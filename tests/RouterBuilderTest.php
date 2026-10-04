<?php
declare(strict_types=1);

use Raxos\Http\HttpResponse;
use Raxos\OpenAPI\RouterBuilder;
use Raxos\Router\DynamicRouter;
use Raxos\Router\Router;
use RaxosTests\OpenAPI\QueryContractsController;
use RaxosTests\OpenAPI\UnitApiController;
use RaxosTests\OpenAPI\UnitHiddenController;
use function RaxosTests\OpenAPI\documentData;

covers(RouterBuilder::class);

it('documents each operation using its own middleware, query aliases, filters and responses', function (): void {
    $builder = new RouterBuilder(Router::createFromControllers(null, [UnitApiController::class, UnitHiddenController::class]));
    $builder->build();
    $paths = documentData($builder->paths);
    expect(array_keys($paths))->toBe(['/units/static', '/units/{id}/{identifier}'])->and($paths)->not->toHaveKey('/secret');
    $path = $paths['/units/{id}/{identifier}'];
    expect(array_column($path['parameters'], 'name'))->toBe(['id', 'identifier']);
    $parameters = array_column($path['get']['parameters'], null, 'name');
    expect($parameters)->toHaveKeys(['X-Unit', 'session', 'search', 'group', 'created_after', 'created_before'])
        ->and($parameters)->not->toHaveKeys(['free', 'q'])
        ->and($parameters['created_after']['schema'])->toBe(['type' => 'string', 'format' => 'date-time'])
        ->and(array_count_values(array_column($path['get']['parameters'], 'name'))['group'])->toBe(1)
        ->and($path['post']['parameters'])->toBe([])
        ->and($path['get']['security'])->toBe([['bearer' => []], ['oauth' => ['read']]])
        ->and($path['get']['responses'][200])->toHaveKey('$ref')
        ->and($path['post']['responses'][201])->toBe(['description' => 'Created'])
        ->and($path['post']['requestBody'])->toMatchArray(['description' => 'Input', 'required' => true])
        ->and($path['post']['requestBody']['content']['application/json']['schema']['$ref'])->toContain('UnitRequest');
    expect($paths['/units/static'])->toHaveKeys(['put', 'patch', 'delete', 'options', 'head'])
        ->and($paths['/units/static']['put']['requestBody'])->toBe(['description' => 'Optional', 'required' => false]);
    $builder->build();
    expect(documentData($builder->paths))->toBe($paths);
});

it('selects only the requested controllers and ignores closure routes', function (): void {
    $router = Router::createFromControllers(null, [UnitApiController::class, UnitHiddenController::class]);

    $builder = new RouterBuilder($router, controllers: [UnitHiddenController::class]);
    $builder->build();
    expect($builder->paths->toArray())->toBe([]);
    $dynamic = new DynamicRouter();
    $dynamic->get('/closure', static fn(): HttpResponse => throw new LogicException('Must not execute.'));
    $builder = new RouterBuilder($dynamic);
    $builder->build();
    expect($builder->paths->toArray())->toBe([]);
});

it('infers required numeric query fields, nullable values, enum arrays and defaults', function (): void {
    $builder = new RouterBuilder(Router::createFromControllers(null, [QueryContractsController::class]));
    $builder->build();
    $paths = documentData($builder->paths);
    $fields = array_column($paths['/query-contracts']['get']['parameters'], null, 'name');
    expect($fields['page_size']['required'])->toBeTrue()->and($fields['page_size']['schema']['type'])->toBe('integer')
        ->and($fields['term']['required'])->toBeFalse()->and($fields['term']['schema']['type'])->toContain('null', 'string')
        ->and($fields['states']['required'])->toBeFalse()->and($fields['states']['schema']['items'])->toHaveKey('$ref')
        ->and($fields['offset']['required'])->toBeFalse()->and($fields['offset']['schema']['default'])->toBe(0);
});
