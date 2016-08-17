<?php
namespace SimpleSignRequest;

use \Psr\Http\Message\ServerRequestInterface;

/**
 * Description of Signature
 *
 * @author mickael
 */
class Signature implements SignatureInterface{
    
    protected $secret = null;
    protected $token = null;
    protected $timestamp = null;
    protected $methodHttp = null;
    protected $headersCustom = array();
    protected $hash = null;
    protected $once = null;
    /**
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    protected $request = null;
    
    public function __construct(ServerRequestInterface $request, $secret, array $headersCustom = array()) {
        $this->secret = $secret;
        $this->token = current($request->getHeader('X-API-token'));
        $this->once =  current($request->getHeader('X-API-once'));
        $this->timestamp =  current($request->getHeader('X-API-timestamp'));
        $this->headersCustom = $headersCustom;
        $this->methodHttp = $request->getMethod();
        $this->uri = $request->getUri();
        $this->request = $request;
        
        $this->checkRequiredProperties();
        $this->generateHash();
    }
    
    protected function checkRequiredProperties(){
        $mapProperties = ['secret','token','timestamp','methodHttp', 'once'];
        
        foreach($mapProperties as $prop){
            if($this->{$prop} == null){
                throw new Exception\SignaturePropertyIsNull("Property $prop is required !");
            }
        }
    }
    
    public function getSecret() {
        return $this->secret;
    }

    public function getToken() {
        return $this->token;
    }

    public function getTimestamp() {
        return $this->timestamp;
    }

    public function getHeadersCustom() {
        return $this->headersCustom;
    }

    public function setSecret($secret) {
        $this->secret = $secret;
    }

    public function setToken($token) {
        $this->token = $token;
    }

    public function setTimestamp($timestamp) {
        $this->timestamp = $timestamp;
    }

    public function setHeadersCustom(array $headersCustom = array()) {
        $this->headersCustom = $headersCustom;
    }
    
    /**
     * HMAC_SHA256(METHOD + TIME + TOKEN + ONCE + URI, SECRET)
     */
    public function generateHash(){
        $data = $this->methodHttp 
                . $this->timestamp 
                . $this->token
                . $this->once
                . $this->uri;
        
        if(count($this->headersCustom) > 0){
            $data .= implode('',$this->headersCustom);
        }
        
        $this->hash = base64_encode(hash_hmac("sha256", $data, $this->secret,true));
        
    }
    
    public function getHash() {
        return $this->hash;
    }
    
    function getMethodHttp() {
        return $this->methodHttp;
    }

    function getOnce() {
        return $this->once;
    }

    function getRequest() {
        return $this->request;
    }

    function setMethodHttp($methodHttp) {
        $this->methodHttp = $methodHttp;
    }

    function setOnce($once) {
        $this->once = $once;
    }

    function setRequest(\Psr\Http\Message\ServerRequestInterface $request) {
        $this->request = $request;
    }



}
