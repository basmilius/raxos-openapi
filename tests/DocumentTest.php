<?php
declare(strict_types=1);

use Raxos\OpenAPI\{OpenAPI, RouterBuilder};
use Raxos\OpenAPI\Definition\{Components, Info};
use Raxos\OpenAPI\Tests\Fixtures\{JsonTree, TreeController};
use Raxos\Router\Router;

it('documents attributed routes and recursive responses without executing handlers', function (): void {
    $builder = new RouterBuilder(Router::createFromControllers(null, [TreeController::class]));
    $builder->build();
    $document = new OpenAPI(new Info('Trees', '3.2.0'), paths: $builder->paths->toArray(), components: new Components(schemas: $builder->schemas->toArray(), responses: $builder->responses->toArray()));
    $data = json_decode($document->getJSON(), true, 512, JSON_THROW_ON_ERROR);
    expect(array_keys($data['paths']))->toBe(['/trees/{id}'])
        ->and($data['paths']['/trees/{id}']['get']['operationId'])->toBe('getTree')
        ->and($data['paths']['/trees/{id}']['parameters'][0])->toMatchArray(['name' => 'id', 'in' => 'path', 'required' => true])
        ->and($data['paths']['/trees/{id}']['get']['responses'])->toHaveKey(200)
        ->and($data['components']['schemas'])->toHaveKey(str_replace('\\', '.', JsonTree::class))
        ->and($data['paths']['/trees/{id}']['get']['responses'][200]['$ref'])->toBe('#/components/responses/' . str_replace('\\', '.', JsonTree::class));
});

it('respects an explicit controller selection when generating documentation', function (): void {
    $builder = new RouterBuilder(Router::createFromControllers(null, [TreeController::class]), controllers: []);
    $builder->build();
    expect($builder->paths->toArray())->toBe([]);
});
