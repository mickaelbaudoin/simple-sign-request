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
    
    protected $expire;
    
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
    
    public function __construct(array $header = array(), $secret, $expire = '5min') {
        $this->headersCustom = $header;
        if(!is_string($secret)){
            throw new Exception\NotStringException('Parameter secret is not string !');
        }
        
        if(!is_string($expire)){
            throw new Exception\NotStringException('Parameter expire is not string !');
        }
        
        $this->secret = $secret;
        $this->expire = $expire;
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
        $signatureClient = $request->getHeaders()['X-API-signature'];
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
        //calculer par rapport au timestampp envoyer et au timstamp expire si la requette est encore valide
        return true;
    }
}
