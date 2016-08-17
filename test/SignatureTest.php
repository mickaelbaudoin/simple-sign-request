<?php

namespace SimpleSignRequestTest;

use \PHPUnit\Framework\TestCase;

/**
 * Description of SignatureTest
 *
 * @author mickael
 */
class SignatureTest extends TestCase{
    
    const SECRET = '123456';
    const TOKEN = 'toto';
    const TIMESTAMP = 1471365063;
    const SIGNATURE = 'sldps12sdsds544sd';
    const ONCE = 1536987;
    
    public function testRequiredPropertiesNotSend(){
        try{
            $header = [];
            $request = new \GuzzleHttp\Psr7\ServerRequest('GET', 'https://localhost/api/article/12', $header);
            $signature = new \SimpleSignRequest\Signature($request, self::SECRET);
            
        } catch (\SimpleSignRequest\Exception\SignaturePropertyIsNull $ex) {
             $this->assertTrue(true);
        }
        
    }
    
    public function testRequiredPropertiesCorrect(){
        try{
            $header = [
            'X-API-token' => self::TOKEN,
            'X-API-timestamp' => self::TIMESTAMP,
            'X-API-signature' => self::SIGNATURE,
            'X-API-once' => self::ONCE
                ];
            $request = new \GuzzleHttp\Psr7\ServerRequest('GET', 'https://localhost/api/article/12', $header);
            $signature = new \SimpleSignRequest\Signature($request, self::SECRET);
            $this->assertTrue(true);
            
        } catch (\SimpleSignRequest\Exception\SignaturePropertyIsNull $ex) {
             echo "\n Exception Message (Line " . __LINE__ . ") : " . $ex->getMessage();
             $this->assertTrue(false);
        }
    }
}
