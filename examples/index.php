<?php

use HackerBoy\JsonApi\Examples\Models\Post;
use HackerBoy\JsonApi\Examples\Models\Comment;
use HackerBoy\JsonApi\Examples\Resources\PostResource;
use HackerBoy\JsonApi\Examples\Resources\CommentResource;

$autoload = '../vendor/autoload.php';

if (!file_exists($autoload)) {
    exit('Run composer first');
}

// Autoload
require $autoload;

$case = @$_GET['case'];

// Make some test objects
$comment1 = new Comment([
    'id' => 1,
    'post_id' => 1,
    'content' => 'This is comment 1'
]);

$comment2 = new Comment([
    'id' => 2,
    'post_id' => 1,
    'content' => 'This is comment 2'
]);

$comment3 = new Comment([
    'id' => 3,
    'post_id' => 2,
    'content' => 'This is comment 3'
]);

$post1 = new Post([
    'id' => 1,
    'title' => 'Post 1 title',
    'content' => 'Post 1 content',
    'comments' => [$comment1, $comment2]
]);

$post2 = new Post([
    'id' => 2,
    'title' => 'Post 2 title',
    'content' => 'Post 2 content',
    'comments' => $comment3
]);


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

if ($case === 'single-resource') {

    $document->setData($post1);

} elseif ($case === 'resource-collection') {

    $document->setData([
        $post1,
        $post2
    ]);

    $pagination = $document->makePagination([
        'first' => $document->getUrl('blah-blah'),
        'last' => $document->getUrl('bloh-blah'),
        'prev' => $document->getUrl('bleh-bloh'),
        'next' => $document->getUrl('blah-bleh')
    ]);

    $document->setLinks($pagination);

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
    ])->setLinks([
        'self' => $document->getUrl('bullshit')
    ]);

} else {

    echo '<h1>Examples:</h1><br />
    <a href="?case=single-resource" target="_blank">Return data with a single resource</a><br />
    <a href="?case=resource-collection" target="_blank">Return data with a collection of resource and pagination links</a><br />
    <a href="?case=default" target="_blank">Return document with data, relationships, included data, meta and links</a><br />
    <a href="?case=get-relationships" target="_blank">Return data as relationships</a><br />
    <a href="?case=show-an-error" target="_blank">Return an error</a><br />
    <a href="?case=show-errors" target="_blank">Return multiple errors</a><br />
    <a href="?case=document-to-array" target="_blank">Test $document->toArray()</a><br />
    <a href="?case=element-to-array" target="_blank">Test $element->toArray()</a><br />
    <a href="?case=resource-to-array" target="_blank">Test $resource->toArray()</a><br />
    ';

}

if ($case) {
    header('Content-Type: application/vnd.api+json');
    echo $document->toJson();
}