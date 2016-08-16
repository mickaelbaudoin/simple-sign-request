<?php

namespace SimpleSignRequestTest;

use \PHPUnit\Framework\TestCase;

/**
 * Description of SignatureRequestMiddlewareTest
 *
 * @author mickael
 */
class SignatureRequestMiddlewareTest extends TestCase{
    const SECRET = '123456';
    const TOKEN = 'toto';
    const TIMESTAMP = 13132131311;
    const SIGNATURE = 'sldps12sdsds544sd';
    const ONCE = 1536987;
    
    public function testHeaderRequiredNotSet(){
        try{
            $header = [];
            $request = new \GuzzleHttp\Psr7\ServerRequest('GET', 'https://localhost/api/article/12', $header);

            $response = new \GuzzleHttp\Psr7\Response();
            $callback = function($req, $res) use ($response) {return $response;};

            $middleware = new \SimpleSignRequest\SignatureRequestMiddleware($header, self::SECRET);
            $middleware($request,$response,$callback);
            
        }catch(\SimpleSignRequest\Exception\HeaderMissingException $e){
            $this->assertTrue(true);
        }
        
    }
    
    public function testRequestWithSignatureFalse(){
        $header = [
            'X-API-token' => self::TOKEN,
            'X-API-timestamp' => self::TIMESTAMP,
            'X-API-signature' => self::SIGNATURE,
            'X-API-once' => self::ONCE
                ];
        $request = new \GuzzleHttp\Psr7\ServerRequest('GET', 'https://localhost/api/article/12', $header);
        
        $response = new \GuzzleHttp\Psr7\Response();
        $callback = function($req, $res) use ($response) {return $response;};
        
        $middleware = new \SimpleSignRequest\SignatureRequestMiddleware($header, self::SECRET);
        $reponseServer = $middleware($request,$response,$callback);
        
        $this->assertEquals(403, $reponseServer->getStatusCode());
    }
    
    public function testRequestWithTimestampOld(){
        //Tout générer correctement mais en indiquant un vieux timestamp
    }
    
    public function testRequestWithSecretFalse(){
        //Générer la signature avec une clef differente du serveur
    }
    
    public function testRequestCorrect(){
        //Générer une signature et un timestamp correzct
    }
}
