<?php

namespace HackerBoy\JsonApi\Examples\Resources;

use HackerBoy\JsonApi\Abstracts\Resource;

class CommentResource extends Resource {

    protected $type = 'comments';

    public function getId()
    {
        return $this->model->id;
    } 

    public function getAttributes()
    {
        return [
            'post_id' => $this->model->post_id,
            'content' => $this->model->content
        ];
    }
    
    public function getRelationships()
    {
        return [
            'post' => $this->model->post
        ];
    }
}