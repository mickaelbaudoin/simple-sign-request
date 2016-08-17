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
        return new SignatureRequestMiddleware($secret);
    }
}
```

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
            SignatureRequestMiddleware::class => SignatureRequestMiddlewareFactory::class
           
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
                SignatureRequestMiddleware::class,
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

#### Javascript

## Examples of creationg base64 hashes using HMAC SHA256 in different language
http://www.jokecamp.com/blog/examples-of-creating-base64-hashes-using-hmac-sha256-in-different-languages/
