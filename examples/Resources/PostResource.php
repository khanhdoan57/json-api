<?php

namespace HackerBoy\JsonApi\Examples\Resources;

use HackerBoy\JsonApi\Abstracts\Resource;

class PostResource extends Resource {

    protected $type = 'posts';

    public function getId()
    {
        return $this->model->id;
    } 

    public function getAttributes()
    {
        return [
            'title' => $this->model->title,
            'content' => $this->model->content
        ];
    }

    public function getRelationships()
    {
        return [
            'comments' => $this->model->comments,
            /** or
            'comments' => [
                'meta' => [
                    'key' => 'value'
                ],
                'links' => [
                    'self' => '/path'
                ],
                'data' => $this->model->comments
            ]
            */
        ];
    }

    public function getMeta()
    {
        return [
            'post-meta-test' => 'This is title: '.$this->model->title
        ];
    }
}