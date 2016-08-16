<?php

namespace SimpleSignRequest;

use \Psr\Http\Message\ServerRequestInterface;

/**
 * Description of SignatureFactory
 *
 * @author mickael baudoin
 */
class SignatureFactory {
    
    public static function generateSignature(ServerRequestInterface $request,$secret, array $headersCustom = array()){
        return new Signature($request, $secret, $headersCustom);
    }
}
