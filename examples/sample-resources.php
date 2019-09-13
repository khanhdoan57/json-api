<?php

use HackerBoy\JsonApi\Examples\Models\Post;
use HackerBoy\JsonApi\Examples\Models\Comment;
use HackerBoy\JsonApi\Examples\Resources\PostResource;
use HackerBoy\JsonApi\Examples\Resources\CommentResource;

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
    'comments' => [$comment3]
]);

$comment1->post = $post1;
$comment2->post = $post1;
$comment3->post = $post2;