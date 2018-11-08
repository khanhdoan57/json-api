<?php

namespace HackerBoy\JsonApi\Examples\Resources;

use HackerBoy\JsonApi\Abstracts\Resource;

class CommentResource extends Resource {

    protected $type = 'comments';

    public function getId($comment)
    {
        return $comment->id;
    } 

    public function getAttributes($comment)
    {
        return [
            'post_id' => $comment->post_id,
            'content' => $comment->content
        ];
    }
    
}