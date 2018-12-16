<?php

use HackerBoy\JsonApi\Examples\Models\Post;
use HackerBoy\JsonApi\Examples\Models\Comment;
use HackerBoy\JsonApi\Examples\Resources\PostResource;
use HackerBoy\JsonApi\Examples\Resources\CommentResource;

require 'bootstrap.php';

// Make a new document
$config = [
    'resource_map' => [
        Post::class => PostResource::class,
        Comment::class => CommentResource::class,
    ],
    'api_url' => 'http://example.com/api',
    'auto_set_links' => true
];

$document = new HackerBoy\JsonApi\Document($config);

// $config can be the used as normal document but it is optional. 
// If you only work with flexible resource, resource_map isn't required
$flexibleDocument = new HackerBoy\JsonApi\Flexible\Document($config); 

if ($case === 'single-resource') {

    $document->setData($post1);

} elseif ($case === 'resource-collection') {

    $document->setData([
        $post1,
        $post1, // Duplicated resource wont be added
        $post2
    ]);

    $pagination = $document->makePagination([
        'first' => $document->getUrl('blah-blah'),
        'last' => $document->getUrl('bloh-blah'),
        'prev' => $document->getUrl('bleh-bloh'),
        'next' => $document->getUrl('blah-bleh')
    ]);

    $document->setLinks($pagination);
    $document->addLinks([
        'test-add-links' => '/add-methods-will-append'
    ]);

} elseif ($case === 'test-included-mixed-resources') {

    $document->setData($post1);
    $document->setIncluded([$comment1, $comment2]);
    $document->addIncluded($post2);
    $document->addIncluded($post2); // Duplicated - wont be added twice
    $document->addIncluded(null); // This code will be ignored
    $document->addIncluded([]); // This code will be ignored

} elseif ($case === 'get-relationships') {

    // To use with fetch relationships request like: /api/posts/1/relationships/comments
    $document->setData([
        $comment1, $comment2
    ], 'relationship');

} elseif ($case === 'show-an-error') {

    $document->setErrors([
        'code' => 123,
        'status' => 500,
        'title' => 'Error 500 example',
        'detail' => 'These error fields are all optional, you may only set one.'
    ]);

    header('HTTP/1.1 500 Internal Server Error');

} elseif ($case === 'show-errors') {

    $document->setErrors([
        [
            'code' => 456,
            'status' => 500,
            'title' => 'Error 1',
            'meta' => [
                'key' => 'value'
            ]
        ],
        [
            'code' => 234,
            'status' => 403,
            'title' => 'Example error',
            'detail' => 'Example error'
        ]
    ]);

    $document->addErrors([
        'title' => 'Add another error'
    ]);

    header('HTTP/1.0 403 Forbidden');

} elseif ($case === 'document-to-array') {

    $document->setData([$post1, $post2]);

    var_dump($document->toArray());
    exit;

} elseif ($case === 'element-to-array') {

    $testElement = $document->makeMeta([
        'test-key' => 'test to array function'
    ]);

    var_dump($testElement->toArray());
    exit;

} elseif ($case === 'resource-to-array') {

    $postResource = new PostResource($post1, $document);

    var_dump($postResource->toArray());
    exit;

} elseif ($case === 'default') {

    $document->setData([
        $post1, $post2
    ])->setIncluded([
        $comment1, $comment2, $comment3
    ])->setMeta([
        'key' => 'value'
    ])->addMeta([
        'add-meta' => 'Added by add method'
    ])->setLinks([
        'self' => $document->getUrl('bullshit')
    ])->addLinks([
        'test' => '/added-by-add-method'
    ]);

} elseif ($case === 'single-flexible-resource') {

    $flexibleResource = $flexibleDocument->makeFlexibleResource();
    $flexibleResource->setType('flexible');
    $flexibleResource->setId('abcd');
    $flexibleResource->setAttributes([
        'name' => 'Flexible Resource',
        'data' => '...test...'
    ]);
    $flexibleResource->setLinks([
        'self' => '/flexible',
        'related' => '/flexible-related'
    ]);
    $flexibleResource->setMeta([
        'flexible-key' => 'flexible value'
    ]);

    $flexibleDocument->setData($flexibleResource);
    $displayFlexible = true;

} elseif ($case === 'flexible-collection') {

    $flexibleCollection = [];

    for ($i = 1; $i <= 5; $i++) {
        $flexibleResource = $flexibleDocument->makeFlexibleResource();

        $flexibleResource->setType('flexible-'.$i);
        $flexibleResource->setId($i);
        $flexibleResource->setAttributes([
            'name' => 'Flexible Resource #'.$i,
            'data' => '...test...'.$i
        ]);
        $flexibleResource->setLinks([
            'self' => '/flexible-'.$i,
            'related' => '/flexible-related-'.$i
        ]);
        $flexibleResource->setMeta([
            'flexible-key' => 'flexible value '.$i
        ]);

        $flexibleCollection[] = $flexibleResource;
    }

    // Make a test relationship
    $flexibleCollection[0]->setRelationships([
        'flexible-1' => $flexibleCollection[1]
    ]);

    $flexibleDocument->setData($flexibleCollection);

    // Add mapped resources to included data
    $flexibleCollection[] = $post1;
    $flexibleCollection[] = $comment1;

    $flexibleDocument->setIncluded($flexibleCollection);
    $flexibleDocument->setLinks([
        'flexible-link' => '/flexible-link-url'
    ]);
    $flexibleDocument->setMeta([
        'flexible-meta' => 'abcdef'
    ]);

    $displayFlexible = true;

} elseif ($case === 'flexible-resource-relationship-with-mapped-resource') {

    $flexibleResource = $flexibleDocument->makeFlexibleResource();
    $flexibleResource->setType('test-relationship');
    $flexibleResource->setId(1234);
    $flexibleResource->setRelationships([
        'posts' => $post1,
        'comments' => [$comment1, $comment2]
    ]);

    $flexibleDocument->setData($flexibleResource);
    $flexibleDocument->setIncluded([$post1, $comment1, $comment2]);

    $displayFlexible = true;

} else {

    echo '<h1>Examples:</h1><br />
    <a href="?case=single-resource" target="_blank">Return data with a single resource</a><br />
    <a href="?case=resource-collection" target="_blank">Return data with a collection of resource and pagination links</a><br />
    <a href="?case=default" target="_blank">Return document with data, relationships, included data, meta and links</a><br />
    <a href="?case=get-relationships" target="_blank">Return data as relationships</a><br />
    <a href="?case=test-included-mixed-resources" target="_blank">Test included data has mixed resources</a><br />
    <a href="?case=show-an-error" target="_blank">Return an error</a><br />
    <a href="?case=show-errors" target="_blank">Return multiple errors</a><br />
    <a href="?case=document-to-array" target="_blank">Test $document->toArray()</a><br />
    <a href="?case=element-to-array" target="_blank">Test $element->toArray()</a><br />
    <a href="?case=resource-to-array" target="_blank">Test $resource->toArray()</a><br />
    <br />
    <h1>Flexible document example:</h1>
    <p>Flexible document can be used exactly like normal document, but $config is optional, flexible resource allowed... You can consider it as a "free schema" version of document</p>
    <p>Flexible document might be helpful for projects with no ORM, build JSON API data quickly without configuration, build JSON API data to POST to another JSON API endpoint...</p>
    <p>Flexible document is not recommended anyway, as it allows to build a document in a free way. So use it carefully and wisely.</p>
    <a href="?case=single-flexible-resource" target="_blank">Single flexible resource</a><br />
    <a href="?case=flexible-collection" target="_blank">Collection of flexible resource</a><br />
    <a href="?case=flexible-resource-relationship-with-mapped-resource" target="_blank">Flexible resource has relationship with mapped resource</a><br />
    ';

}

if ($case) {
    header('Content-Type: application/vnd.api+json');

    if (isset($displayFlexible)) {
        exit($flexibleDocument->toJson());
    }

    echo $document->toJson();
}