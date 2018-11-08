<?php

namespace HackerBoy\JsonApi\Examples\Models;

class Comment {

    public $id;
    public $post_id;
    public $content;

    public function __construct($data)
    {
        $this->id = $data['id'];
        $this->post_id = $data['post_id'];
        $this->content = $data['content'];
    }

}