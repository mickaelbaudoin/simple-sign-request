<?php

declare(strict_types=1);

namespace SimpleSignRequest;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Description of SignatureRequestMiddleware
 *
 * @author mickael baudoin
 */
class SignatureRequestMiddleware {
    
    protected $headersCustom;
    
    protected $secret;
    
    protected $expireSecond;
    
    const REQUIRED_HEADERS = array('X-API-token', 'X-API-timestamp', 'X-API-signature', 'X-API-once');
    
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next) {
        
        if($this->checkRequiredHeader($request)){
            if($this->checkTimestamp($request)){
                if($this->checkSignature($request)){
                    return $next($request, $response);
                }
            }
        }
        
        $response = $response->withStatus(403);
        $response->getBody()->write(json_encode(['message' => 'Unauthorized', 'code' => 403]));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function __construct($secret, array $headersCustom = array(), $expireSecond = 60) {
        $this->headersCustom = $headersCustom;
        if(!is_string($secret)){
            throw new Exception\NotStringException('Parameter secret is not string !');
        }
        
        if(!is_integer($expireSecond)){
            throw new Exception\NotIntegerException('Parameter expire is not integer !');
        }
        
        $this->secret = $secret;
        $this->expireSecond = $expireSecond;
    }
    
    /**
     * Permet de vérifier que les headers obligatoires sont bien présent
     * 
     * @param ServerRequestInterface $request
     * @return boolean
     * @throws Exception\HeaderMissingException
     */
    protected function checkRequiredHeader(ServerRequestInterface $request){
        $headers = $request->getHeaders();
        
        foreach(self::REQUIRED_HEADERS as $header){
            if(!array_key_exists($header, $headers)){
                throw new Exception\HeaderMissingException("Header $header is missing !");
            }
        }
        
        return true;
    }
    
    /**
     * Permet de vérifier l'intégrité et l'autenticité de la signature envoyer par le client
     *  
     * @param ServerRequestInterface $request
     * @return boolean
     */
    protected function checkSignature(ServerRequestInterface $request){
        $signatureClient = $request->getHeader('X-API-signature')[0];
        $signatureServer = SignatureFactory::generateSignature($request, $this->secret, $this->headersCustom);
        
        return $signatureClient == $signatureServer->getHash();
    }
    
    /**
     * Permet de vérifier que la requette soit encore valide
     * 
     * @param ServerRequestInterface $request
     * @return boolean
     */
    protected function checkTimestamp(ServerRequestInterface $request){
        $timeCurrent = time();
        $timeEnd =  $timeCurrent + $this->expireSecond;
        $timeBegin = $timeCurrent - $this->expireSecond;
        $timeSend = (int) $request->getHeader('X-API-timestamp')[0];
        
        return ($timeSend < $timeEnd && $timeSend > $timeBegin);
    }
}
