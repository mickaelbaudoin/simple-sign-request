## Synospis
Check signature request client with hmac_sha256 encoded base64

## Code example

### ServerSide

#### With zend-expressive

- Create Factory middleware for injected secret shared

```
<?php

namespace Foo;

use Interop\Container\ContainerInterface;

/**
 * Description of SignatureRequestMiddlewareFactory
 */
class SignatureRequestMiddlewareFactory {
    
    public function __invoke(ContainerInterface $container)
    {
        $middleware = new \MB\SignatureRequestMiddleware($secret);
        $middleware->addIgnorePath('/auth');
        
        return $middleware;
    }
}
```
Optional parameters for SignatureRequestMiddleware constructor :
new \MB\SignatureRequestMiddleware($secret,$headersCustom = array(), $expireSecond = 60);

- Edit config for configured Factories and Middlewares

middleware-pipeline.global.php
```
<?php
use Zend\Expressive\Container\ApplicationFactory;
use Zend\Expressive\Helper;
return [
    'dependencies' => [
        'factories' => [
            Helper\ServerUrlMiddleware::class => Helper\ServerUrlMiddlewareFactory::class,
            Helper\UrlHelperMiddleware::class => Helper\UrlHelperMiddlewareFactory::class,
            MB\SignatureRequestMiddleware::class => SignatureRequestMiddlewareFactory::class
           
        ],
    ],
    // This can be used to seed pre- and/or post-routing middleware
    'middleware_pipeline' => [
    .
    .
            'routing' => [
            'middleware' => [
                ApplicationFactory::ROUTING_MIDDLEWARE,
                Helper\UrlHelperMiddleware::class,
                MB\SignatureRequestMiddleware::class,
                // Add more middleware here that needs to introspect the routing
                // results; this might include:
                // - route-based authentication
                // - route-based validation
                // - etc.
                ApplicationFactory::DISPATCH_MIDDLEWARE,
            ],
            'priority' => 1,
        ],
        .
        .
        .
    
```

### Client

#### PHP
```
$secret = 'secret';
$method = 'GET';
$timestamp = time();
$token = '123456789';
$once = '2536';
$uri = 'http://localhost/article/12';

//headers required
$headers = [
    'X-API-token' => $token,
    'X-API-timestamp' => $timestamp,
    'X-API-once' => $once
];

//headers custom (optional)
$headersCustom = [
    'X-API-realm' => 'foo'
];

//Generated signature
$data = ($method . $timestamp . $token . $once . $uri);
if(count($headersCustom) > 0){
    foreach($headersCustom as $value){
        $data .= $value;
    }
}
$hash = base64_encode(hash_hmac('sha256', $data, $secret,true));

$headers['X-API-signature'] = $hash;

//Sending request with curl
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,$uri);
curl_setopt($ch, CURLOPT_GET, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$server_output = curl_exec ($ch);

curl_close ($ch);

```

## Examples of creationg base64 hashes using HMAC SHA256 in different language
http://www.jokecamp.com/blog/examples-of-creating-base64-hashes-using-hmac-sha256-in-different-languages/
