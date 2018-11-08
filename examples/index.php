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
    'api_url' => 'http://example.com',
];

$document = new HackerBoy\JsonApi\Document($config);

if ($case === 'single-resource') {

    $document->setData($post1);

} elseif ($case === 'resource-collection') {

    $document->setData([
        $post1,
        $post2
    ]);

} else {

    $document->setData([
        $post1, $post2
    ])->setIncluded([
        $comment1, $comment2, $comment3
    ])->setMeta([
        'key' => 'value'
    ])->setLinks([
        'next' => '/bullshit'
    ]);

}

header('Content-Type', 'application/vnd.api+json');
echo json_encode($document, JSON_PRETTY_PRINT);