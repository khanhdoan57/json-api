# Packagist: hackerboy/json-api
Making JSON API implementation (server side) easiest for you 
## Install
```
composer require hackerboy/json-api
```

# Run examples:
- Example code: /example/index.php
- Guide to set up /examples/index.php to see some examples of uses.

```
git clone https://github.com/hackerboydotcom/json-api ./hackerboy-json-api;
cd hackerboy-json-api;
composer install --dev;
```
Then config your localhost nginx/apache to access [LOCALHOST_PATH]/examples/index.php

# How to use?
## Create your resource schema
For example, I'm gonna talk in a Laravel project context. Firstly, let's create a resource file: /app/Http/JsonApiResources/UserResource.php

```
<?php
namespace App\Http\JsonApiResources;
use HackerBoy\JsonApi\Abstracts\Resource;

class UserResource extends Resource {

    protected $type = 'users';

    public function getId($user)
    {
        return $user->id;
    } 

    public function getAttributes($user)
    {
        return [
            'name' => $user->name,
            'email' => $user->email
        ];
    }

    /**
    * Meta is optional
    */
    public function getMeta($user)
    {
        return [
            'meta-is-optional' => $user->some_value
        ];
    }
}
```

## Configuration and mapping your resources
Now we can easily generate a JSON API document object like this:

```
<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use HackerBoy\JsonApi\Document;

class UserController extends Controller {
  
    public function index()
    {
        // User to return
        $user = \App\User::find(1);
        $users = \App\User::take(10)->get();
        
        // Config and mapping
        $config = [
            'resource_map' => [
                \App\User::class => \App\Http\JsonApiResources\UserResource::class
                // Map your other model => resource
            ],
            'api_url' => 'http://example.com',
            'auto_set_links' => true, // Enable this will automatically add links to your document according to JSON API standard
        ];
         
        // Let's test it
        $document = new Document($config);
        $document->setData($user) // or set data as a collection by using ->setData($users)
                  ->setMeta(['key' => 'value']);
        
        return response()->json($document)->header('Content-Type', 'application/vnd.api+json');
    }
}

```

## Document set & add methods

The difference between "set methods" and "add methods" are is: "Set methods" will override the data while "add methods" will append to data.

Available "set" methods from $document object are: 
+ setData($resourceOrCollection, $type = 'resource') // Default $type = 'resource', in case you need to return data as relationship object, change $type to 'relationship'
+ setIncluded($resourceOrCollection)
+ setErrors($errors) // Array or HackerBoy\JsonApi\Elements\Error object - single error or multiple errors data will both work for this method
+ setLinks($links) // Array of link data or HackerBoy\JsonApi\Elements\Links object
+ setMeta($meta) // Array of meta data or HackerBoy\JsonApi\Elements\Meta object

Available "add" methods from $document object are:
+ addIncluded($resourceOrCollection)
+ addErrors($errors) // Array or HackerBoy\JsonApi\Elements\Error object - single error or multiple errors data will both work for this method
+ addLinks($links) // Array of link data or HackerBoy\JsonApi\Elements\Links object
+ addMeta($meta) // Array of meta data or HackerBoy\JsonApi\Elements\Meta object

Example:
```
<?php

$document->setData([$post1, $post2]) // or ->setData($post) will also work
    ->setIncluded([$comment1, $comment2])
    ->setMeta([
            'meta-key' => 'meta-value',
            'meta-key-2' => 'value 2'
        ])
    ->setLinks($document->makePagination([
            'first' => $document->getUrl('first-link'),
            'last' => $document->getUrl('last-link'),
            'prev' => $document->getUrl('prev-link'),
            'next' => $document->getUrl('last-link'),
        ]));
```

## Implement relationships
Simply return an array in getRelationships() method

```
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
        $relationships = [
            'author' => $post->author // Post has relationship with author
        ];

        // Check if post has comments
        if (isset($post->comments) and count($post->comments)) {

            // Add comments to relationships
            $relationships['comments'] = $post->comments;

        }

        return $relationships;
    }
}
```

## Set data as relationships
Second param allows you to set data as relationship (for request like: /api/posts/1/relationships/comments)
```
<?php

// Set data as relationship
$document->setData($resourceOrCollection, 'relationship');
```

## toArray() and toJson() method
New methods in v1.1. Available for document, elements and resources
```
<?php
$data = $document->toArray();
$json = $document->toJson();
```

## Easily create element for your document
Suppose that we created a $document object

### Create error
```
<?php

// Create an error
$errorData = [
    'id' => 123,
    'status' => 500,
    'code' => 456,
    'title' => 'Test error'
];

// Return an error
$error = $document->makeError($errorData);

// Return multiple errors
$errors = [$document->makeError($errorData), $document->makeError($errorData)];

// Attach error to document
$document->setErrors($error);
// Or
$document->setErrors($errors);
// It'll even work if you just put in an array data
$document->setErrors($errorData);

```

### Create links
```
<?php

$linkData = [
        'self' => $document->getUrl('self-url'),
        'ralated' => $document->getUrl('related-url')
    ];

// Create links
$links = $document->makeLinks($linkData);

// Attach links to document
$document->setLinks($links);
// this will also work
$document->setLinks($linkData);

// Create pagination
$pagination = $document->makePagination([
        'first' => $document->getUrl('first-link'),
        'last' => $document->getUrl('last-link'),
        'prev' => $document->getUrl('prev-link'),
        'next' => $document->getUrl('last-link'),
    ]);

// Attach pagination to document
$document->setLinks($pagination);
```

### Create other elements
It'll work the same way, available methods are: 

+ makeError(), 
+ makeErrorResource()
+ makeLink() (Create link object inside links data: https://jsonapi.org/format/#document-links)
+ makeLinks()
+ makePagination() (Make special Links object with pagination standard required)
+ makeMeta()
+ makeRelationship() (Create relationship object inside relationships data: https://jsonapi.org/format/#document-resource-object-relationships)
+ makeRelationships()

You can see more examples in /examples/index.php

## Flexible document object
- Flexible document can be used exactly like normal document, but $config is optional, flexible resource allowed... You can consider it as a "free schema" version of document.
- Flexible document can use $config as normal document, then you can use it to work with flexible resource and mapped resource in the same document.
- Flexible document might be helpful for projects with no ORM, build JSON API data quickly without configuration, build JSON API data to POST to another JSON API endpoint...
- Flexible document is not recommended anyway, as it allows to build a document in a free way and may cause unpredicted errors to your API like missing elements, invalid format...etc... So use it carefully and wisely

### Example of flexible document use

```
<?php

$flexibleDocument = new HackerBoy\JsonApi\Flexible\Document; // $config is the same format with normal document but it is optional

// Create a flexible resource
$flexibleResource = $flexibleDocument->makeFlexibleResource();
$flexibleResource->setId(1234);
                ->setType('flexible')
                ->setAttributes([
                        'attribute_name' => 'attribute value'
                    ])
                ->setMeta([
                        'meta-key' => 'meta value'
                    ])
                ->setLinks([
                        'self' => '/link'
                    ]);

// Attach flexible resource to document
$flexibleDocument->setData($flexibleResource); // You can put in a collection as well, all other methods are the same

echo $flexibleDocument->toJson();

```