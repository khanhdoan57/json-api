# Packagist: hackerboy/json-api
Making JSON API implementation (server side) easiest for you 

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
}
```

## Mapping your resources
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
            ]
        ];
         
        // Let's test it
        $document = new Document($config);
        $document->setData($user)
                  ->setMeta(['key' => 'value']);
        
        return response()->json($document)->header('Content-Type', 'application/vnd.api+json');
    }
}

```

You can see more examples in /examples/index.php
