<?php

declare(strict_types=1);

namespace SimpleSignRequest;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Description of SigningCheck
 *
 * @author mickael
 */
class SignatureRequestMiddleware {
    
    protected $header;
    
    protected $secret;
    
    protected $expire;
    
    public function __construct(HeaderCustomInterface $header, string $secret, string $expire = '5min') {
        $this->header = $header;
        $this->secret = $secret;
        $this->expire = $expire;
    }
    
    public function checkSignature(ServerRequestInterface $request){
        //Rendre obligatoire certain header(X-timestamp, X-secret)
        //On vérifit dans un premier temps si le timestamp est valid (selui envoyer dans le header)
        //Generer signature grace au header(header obligatoire + header custom) envoyer par le client (+la clef secret partager)
        //On recupère la signature envoyer par le client
        //On compare les signatures
    }
    
    protected function generateSignature(){
        //Générer la signature grace au header obligatoire et au header custom + clef secret partager
    }
}
