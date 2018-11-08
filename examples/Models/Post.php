<?php

namespace HackerBoy\JsonApi\Examples\Models;

class Post {

    public $id;
    public $title;
    public $content;

    public $comments;

    public function __construct($data)
    {
        $this->id = $data['id'];
        $this->title = $data['title'];
        $this->content = $data['content'];
        $this->comments = $data['comments'];
    }

}