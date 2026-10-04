<?php
declare(strict_types=1);

use Raxos\OpenAPI\Definition\{Info, Operation, Path, Response};
use Raxos\OpenAPI\OpenAPI;
use Symfony\Component\Yaml\Yaml;

covers(OpenAPI::class);

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
