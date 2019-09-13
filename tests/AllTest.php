<?php

use PHPUnit\Framework\TestCase;
use HackerBoy\JsonApi\Document;
use HackerBoy\JsonApi\Examples\Models\Post;
use HackerBoy\JsonApi\Examples\Models\Comment;
use HackerBoy\JsonApi\Examples\Resources\PostResource;
use HackerBoy\JsonApi\Examples\Resources\CommentResource;
use Art4\JsonApiClient\Helper\Parser;

require './examples/sample-resources.php';

// Make a new document
$config = [
    'resource_map' => [
        Post::class => PostResource::class,
        Comment::class => CommentResource::class,
    ],
    'api_url' => 'http://example.com/api',
    'auto_set_links' => true
];

class AllTest extends TestCase
{
    public function testSingleResourceDocument()
    {
        global $config, $post1;

        $document = new Document($config);

        $document->setData($post1);

        $this->assertTrue(Parser::isValidResponseString($document->toJson()));
    }

    public function testResourceCollectionDocument()
    {
        global $config, $post1, $post2;

        $document = new Document($config);

        $document->setData([$post1, $post2]);
        $document->setIncluded(array_merge($post1->comments, $post2->comments));

        $this->assertTrue(Parser::isValidResponseString($document->toJson()));
    }

    public function testIncludedDuplicate()
    {
        global $config, $post1;

        $document = new Document($config);

        $document->setData($post1);
        $document->setIncluded($post1->comments);
        $document->addIncluded($post1->comments);
        $document->addIncluded($post1->comments);

        $documentArray = $document->toArray();

        $this->assertTrue(Parser::isValidResponseString($document->toJson()));
        $this->assertTrue(count($post1->comments) === count($documentArray['included']));
    }

    public function testDataAsRelationships()
    {
        global $config, $comment1, $comment2;

        $singleRelationshipDocument = new Document($config);
        $singleRelationshipDocument->setData($comment1, 'relationship');
        $singleRelationshipDocumentArray = $singleRelationshipDocument->toArray();

        $this->assertTrue(Parser::isValidResponseString($singleRelationshipDocument->toJson()));
        $this->assertArrayHasKey('data', $singleRelationshipDocumentArray);
        $this->assertSame([
            'id' => $comment1->id,
            'type' => 'comments'
        ], $singleRelationshipDocumentArray['data']);

        // Multiple relationships
        $multipleRelationshipDocument = new Document($config);
        $multipleRelationshipDocument->setData([$comment1, $comment2], 'relationships');
        $multipleRelationshipDocument->setIncluded([$comment1->post, $comment2->post]);
        $multipleRelationshipDocumentArray = $multipleRelationshipDocument->toArray();

        $this->assertTrue(Parser::isValidResponseString($multipleRelationshipDocument->toJson()));
        $this->assertArrayHasKey('data', $multipleRelationshipDocumentArray);
        $this->assertTrue(count($multipleRelationshipDocumentArray['data']) === 2);
        $this->assertTrue(!array_key_exists('included', $multipleRelationshipDocumentArray));

    }

    public function testErrorDocument()
    {
        global $config;

        $error = [
            'code' => "123",
            'status' => "500",
            'title' => 'Test Error',
            'detail' => 'Test error detail'
        ];

        $document = new Document($config);
        $document->setErrors($error);

        $this->assertTrue(Parser::isValidResponseString($document->toJson()));
    }

    public function testErrorsDocument()
    {
        global $config;

        $errors = [
            [
                'code' => "123",
                'status' => "500",
                'title' => 'Test Error',
                'detail' => 'Test error detail'
            ],
            [
                'code' => "321",
                'status' => "400",
                'title' => 'Test Error 2',
                'detail' => 'Test error detail 2'
            ],
        ];

        $document = new Document($config);
        $document->setErrors($errors);

        $this->assertTrue(Parser::isValidResponseString($document->toJson()));
    }
}