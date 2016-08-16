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
    const TIMESTAMP = 1471365063;
    const SIGNATURE = 'sldps12sdsds544sd';
    const ONCE = 1536987;
    
    public function testHeaderRequiredNotSet(){
        try{
            $header = [];
            $request = new \GuzzleHttp\Psr7\ServerRequest('GET', 'https://localhost/api/article/12', $header);

            $response = new \GuzzleHttp\Psr7\Response();
            $callback = function($req, $res) use ($response) {return $response;};

            $middleware = new \SimpleSignRequest\SignatureRequestMiddleware(self::SECRET);
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
        
        $middleware = new \SimpleSignRequest\SignatureRequestMiddleware(self::SECRET);
        $reponseServer = $middleware($request,$response,$callback);
        
        $this->assertEquals(403, $reponseServer->getStatusCode());
    }
    
    public function testRequestWithTimestampOld(){
        $method = 'GET';
        $uri = 'https://localhost/api/article/12';
        $signature = $this->generateSignature($method,self::TIMESTAMP,$uri,self::TOKEN, self::ONCE, self::SECRET);
        
        $header = [
            'X-API-token' => self::TOKEN,
            'X-API-timestamp' => self::TIMESTAMP,
            'X-API-signature' => $signature,
            'X-API-once' => self::ONCE
                ];
        $request = new \GuzzleHttp\Psr7\ServerRequest($method, $uri, $header);
        $response = new \GuzzleHttp\Psr7\Response();
        $callback = function($req, $res) use ($response) {return $response;};
        
        $middleware = new \SimpleSignRequest\SignatureRequestMiddleware(self::SECRET);
        $reponseServer = $middleware($request,$response,$callback);
        
        $this->assertEquals(403, $reponseServer->getStatusCode());
    }
    
    public function testRequestWithSecretFalse(){
        //Générer la signature avec une clef differente du serveur
        $method = 'GET';
        $uri = 'https://localhost/api/article/12';
        $signature = $this->generateSignature($method,time(),$uri,self::TOKEN, self::ONCE, 'nimportequoi');
        
        $header = [
            'X-API-token' => self::TOKEN,
            'X-API-timestamp' => self::TIMESTAMP,
            'X-API-signature' => $signature,
            'X-API-once' => self::ONCE
                ];
        $request = new \GuzzleHttp\Psr7\ServerRequest($method, $uri, $header);
        $response = new \GuzzleHttp\Psr7\Response();
        $callback = function($req, $res) use ($response) {return $response;};
        
        $middleware = new \SimpleSignRequest\SignatureRequestMiddleware(self::SECRET);
        $reponseServer = $middleware($request,$response,$callback);
        
        $this->assertEquals(403, $reponseServer->getStatusCode());
    }
    
    public function testRequestWithHeadersAndSignatureCorrect(){
        //Générer une signature et un timestamp correzct
        $method = 'GET';
        $uri = 'https://localhost/api/article/12';
        $timestamp = time();
        $signature = $this->generateSignature($method,$timestamp,$uri,self::TOKEN, self::ONCE, self::SECRET);
        
        $headers = [
            'X-API-token' => self::TOKEN,
            'X-API-timestamp' => $timestamp,
            'X-API-signature' => $signature,
            'X-API-once' => self::ONCE
                ];
        $request = new \GuzzleHttp\Psr7\ServerRequest($method, $uri, $headers);
        $response = new \GuzzleHttp\Psr7\Response();
        $callback = function($req, $res) use ($response) {return $response;};
        
        $middleware = new \SimpleSignRequest\SignatureRequestMiddleware(self::SECRET);
        $reponseServer = $middleware($request,$response,$callback);
        
        $this->assertEquals(200, $reponseServer->getStatusCode());
    }
    
    /**
     * Vérifit qua la requette soit valide avec 
     * un timestamp correcte
     * en tête obligatoire correcte
     * en tête custom
     */
    public function testRequestWithHeadersAndSignatureAndHeadersCustomCorrect(){
        //Générer une signature et un timestamp correzct
        $method = 'GET';
        $uri = 'https://localhost/api/article/12';
        $timestamp = time();
        $headersCustom = ['X-API-realm' => 'toto'];
        $signature = $this->generateSignature($method,$timestamp,$uri,self::TOKEN, self::ONCE, self::SECRET,$headersCustom);
        
        $headers = [
            'X-API-token' => self::TOKEN,
            'X-API-timestamp' => $timestamp,
            'X-API-signature' => $signature,
            'X-API-once' => self::ONCE,
            'X-API-realm' => 'toto'
                ];
        $request = new \GuzzleHttp\Psr7\ServerRequest($method, $uri, array_merge($headers,$headersCustom) );
        $response = new \GuzzleHttp\Psr7\Response();
        $callback = function($req, $res) use ($response) {return $response;};
        
        //On spécifit les headerCustom qu'on a rajouter
        $middleware = new \SimpleSignRequest\SignatureRequestMiddleware(self::SECRET,$headersCustom);
        $reponseServer = $middleware($request,$response,$callback);
        
        $this->assertEquals(200, $reponseServer->getStatusCode());
    }
    
    /**
     * Algo pour générer la signature d'une requette
     * Cet aglo correspond acelui du serveur
     * 
     * @param string $method
     * @param integer $timestamp
     * @param string $uri
     * @param string $token
     * @param string $once
     * @param string $secret
     * @param array $headersCustom
     * @return string hash_mac base64
     */
    private function generateSignature($method,$timestamp,$uri, $token, $once, $secret, array $headersCustom = array()){
        //HMAC_SHA256(METHOD + TIME + TOKEN + ONCE + URI, SECRET)
        $data = ($method . $timestamp . $token . $once . $uri);
        if(count($headersCustom) > 0){
            foreach($headersCustom as $value){
                $data .= $value;
            }
        }
        $hash = base64_encode(hash_hmac('sha256', $data, $secret,true));
        return $hash;
    }
}
