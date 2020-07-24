<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/dotenv-loader.php';

use phpseclib\Crypt\AES;
use phpseclib\Crypt\Random;

// http://phpseclib.sourceforge.net/crypt/2.0/examples.html
$cipher = new AES(AES::MODE_CBC);
$cipher->setKeyLength(256);
$cipher->setKey($_ENV['SHARED_SECRET']);
$cipher->setIV(Random::string($cipher->getBlockLength() >> 3));

function encrypt($data) {
    if($_ENV['ENCRYPT_ACCESS_TOKEN'] === 'true') {
        global $cipher;
        return base64_encode($cipher->encrypt($data));
    }
    return $data;
}

function decrypt($data){
    if($_ENV['ENCRYPT_ACCESS_TOKEN'] === 'true') {
        global $cipher;
        return $cipher->decrypt(base64_decode($data));
    }
    return $data;
}