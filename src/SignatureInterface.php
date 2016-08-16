<?php

namespace SimpleSignRequest;

/**
 *
 * @author mickael
 */
interface SignatureInterface {
    public function getHeadersCustom();
    public function setHeadersCustom(array $headers = array());
    public function getHash();
    public function getSecret();
    public function setSecret($secret);
    public function setToken($token);
    public function getToken();
    public function setTimestamp($time);
    public function getTimestamp();
    public function generateHash();
    public function getMethodHttp();
    public function getOnce();
    public function getRequest();
    public function setMethodHttp($methodHttp);
    public function setOnce($once);
    public function setRequest(\Psr\Http\Message\ServerRequestInterface $request);
}
