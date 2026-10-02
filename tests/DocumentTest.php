<?php
declare(strict_types=1);

use Raxos\OpenAPI\Definition\Components;
use Raxos\OpenAPI\Definition\Info;
use Raxos\OpenAPI\Definition\Operation;
use Raxos\OpenAPI\Definition\Path;
use Raxos\OpenAPI\Definition\Response;
use Raxos\OpenAPI\OpenAPI;
use Raxos\OpenAPI\RouterBuilder;
use Raxos\OpenAPI\Tests\Fixtures\JsonTree;
use Raxos\OpenAPI\Tests\Fixtures\TreeController;
use Raxos\Router\Router;
use Symfony\Component\Yaml\Yaml;

it('exports the same document as JSON and YAML with stable path ordering', function (): void {
    $path = new Path(get: new Operation(responses: [200 => new Response('OK')]));
    $document = new OpenAPI(new Info('Raxos & Co', '3.2.0', description: "Line one\nLine two"), paths: ['/z' => $path, '/a' => $path]);
    $json = json_decode($document->getJSON(), true, 512, JSON_THROW_ON_ERROR);
    expect($json['openapi'])->toBe('3.1.1')
        ->and(array_keys($json['paths']))->toBe(['/a', '/z'])
        ->and($json['info']['title'])->toBe('Raxos & Co')
        ->and($json['paths']['/a']['get']['responses'][200]['description'])->toBe('OK')
        ->and(Yaml::parse($document->getYAML()))->toBe($json);
});

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
