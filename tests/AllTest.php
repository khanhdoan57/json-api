<?php

use PHPUnit\Framework\TestCase;
use HackerBoy\JsonApi\Document;
use HackerBoy\JsonApi\Flexible\Document as FlexibleDocument;
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

        $meta = [
            'test-key' => 'test-value',
            'test2' => 'test value 2'
        ];

        $document = new Document($config);

        $document->setData($post1);
        $document->setMeta($meta);

        $this->assertTrue(is_string($document->toArray()['data']['id']));
        $this->assertTrue(Parser::isValidResponseString($document->toJson()));
        $this->assertSame($meta, $document->toArray()['meta']);
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
            'id' => (string) $comment1->id,
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

        // Error code and status will be converted to (string) automatically due to JSONAPI specs
        $error = [
            'code' => 123,
            'status' => 500,
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
                'code' => 123,
                'status' => 500,
                'title' => 'Test Error',
                'detail' => 'Test error detail'
            ],
            [
                'code' => 321,
                'status' => 400,
                'title' => 'Test Error 2',
                'detail' => 'Test error detail 2'
            ],
        ];

        $document = new Document($config);
        $document->setErrors($errors);

        $this->assertTrue(Parser::isValidResponseString($document->toJson()));
    }

    public function testFlexibleDocument()
    {
        global $config;

        $flexibleDocument = new FlexibleDocument($config);

        // Create new flexible resource
        $flexibleResource = $flexibleDocument->makeFlexibleResource();
        $flexibleResource->setType('tests');
        $flexibleResource->setId(1234);
        $flexibleResource->setAttributes([
            'test' => 12345
        ]);
        $flexibleResource->setMeta([
            'meta1' => 'meta1 value',
            'meta2' => 'meta2 value'
        ]);
        $flexibleResource->setLinks([
            'test-link' => 'http://example.com'
        ]);

        $flexibleDocument->setData($flexibleResource);

        $flexibleDocumentArray = $flexibleDocument->toArray();
        ksort($flexibleDocumentArray['data']);

        $this->assertTrue(Parser::isValidResponseString($flexibleDocument->toJson()));
        $this->assertSame([
            'data' => (function() {

                $sortedArray = [
                    'id' => '1234',
                    'type' => 'tests',
                    'attributes' => [
                        'test' => 12345
                    ],
                    'meta' => [
                        'meta1' => 'meta1 value',
                        'meta2' => 'meta2 value'
                    ],
                    'links' => [
                        'test-link' => 'http://example.com'
                    ]
                ];
                
                ksort($sortedArray);
                
                return $sortedArray;

            })()
        ], $flexibleDocumentArray);
    }

    public function testParseDocumentFromString()
    {

        $jsonapiString = '{
            "data": {
              "type": "articles",
              "id": "1",
              "attributes": {
                "title": "Rails is Omakase"
              },
              "relationships": {
                "author": {
                  "links": {
                    "self": "/articles/1/relationships/author",
                    "related": "/articles/1/author"
                  },
                  "data": { "type": "people", "id": "1" }
                },
                "images": {
                    "data": [
                        {
                            "type": "images",
                            "id": "1"
                        },
                        {
                            "type": "images",
                            "id": "2"
                        }
                    ]
                }
              },
              "meta": {
                "a": "b",
                "c": "d"
              }
            },
            "included": [
                {
                    "type": "people",
                    "id": "1",
                    "attributes": {
                        "name": "John Doe"
                    }
                },
                {
                    "type": "images",
                    "id": "1",
                    "attributes": {
                        "src": "http://example.com/1.jpg"
                    }
                },
                {
                    "type": "images",
                    "id": "2",
                    "attributes": {
                        "src": "http://example.com/2.jpg"
                    }
                }
            ]
        }';

        $document = FlexibleDocument::parseFromString($jsonapiString);
        $this->assertTrue(Parser::isValidResponseString($document->toJson()));

        $inputArray = json_decode($jsonapiString, true);

        $this->assertEqualsCanonicalizing($inputArray, $document->toArray());

        $article = $document->getData();

        $this->assertSame($article->getRelationshipData('author'), $document->getQuery()->where('id', '1')->where('type', 'people')->first());
        $this->assertTrue(is_iterable($article->getRelationships()));
        $this->assertEquals(2, count($article->getMeta()));

        // Test set new attribute
        $newAttribute =  'Test value - '.microtime(true);
        $article->setAttribute('test-new-attribute', $newAttribute);

        // Test get attribute
        $this->assertEquals($newAttribute, $article->getAttribute('test-new-attribute'));
        $this->assertEquals($article->getAttribute('not-found-attribute', 'default value'), 'default value');

        $this->assertTrue($document->toArray()['data']['attributes']['test-new-attribute'] === $newAttribute);

    }

    public function testDocumentQuery()
    {
        global $config, $post1, $post2;

        $document = new Document($config);

        $document->setData([$post1, $post2]);
        $document->setIncluded(array_merge($post1->comments, $post2->comments));

        // Test query
        $post1Resource = $document->getQuery()->where('type', 'posts')->where('id', 1)->first();
        $this->assertTrue($post1Resource->getId() === $post1->id);

        $comment1Resource = $document->getQuery()->where('type', 'comments')->where('id', 1)->first();
        $this->assertTrue($comment1Resource->getId() === 1);

        // Flexible document test
        $jsonapiString = $document->toJson();

        // Make flexible document from string
        $flexibleDocument = FlexibleDocument::parseFromString($jsonapiString);

        // Test query
        $comment1Resource = $flexibleDocument->getQuery()->where([
                                'type' => 'comments',
                                'id' => 1
                            ])->first();
        $post1Resource = $flexibleDocument->getQuery()->where('type', 'posts')->where('id', '1')->first();
        $this->assertTrue($post1Resource->getId() === (string) $post1->id);

        $post1Resource = $flexibleDocument->getQuery()->where('attributes.title', 'Post 1 title')->first();
        $this->assertTrue($post1Resource->getId() === (string) $post1->id);

        $comment1Resource = $flexibleDocument->getQuery()->where('type', 'comments')->where('id', '1')->first();
        $this->assertTrue($comment1Resource->getId() === '1');

        $comments = $post1Resource->getRelationshipData('comments');

        // Expect comment 1 and comment 2
        foreach ($comments as $comment) {
            $this->assertTrue(in_array($comment->getId(), [1, 2]));
        }

        $this->assertTrue($comments->count() === 2);

    }
}