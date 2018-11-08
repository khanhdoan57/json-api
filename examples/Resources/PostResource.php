<?php

namespace HackerBoy\JsonApi\Examples\Resources;

use HackerBoy\JsonApi\Abstracts\Resource;

class PostResource extends Resource {

    protected $type = 'posts';

    public function getId($post)
    {
        return $post->id;
    } 

    public function getAttributes($post)
    {
        return [
            'title' => $post->title,
            'content' => $post->content
        ];
    }

    public function getRelationships($post)
    {
        return [
            'comments' => $post->comments
        ];
    }
}