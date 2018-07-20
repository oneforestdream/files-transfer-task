<?php

namespace App\Helpers;

class SSLHelper
{
    const ENCRYPTION_METHOD = 'aes-256-cbc';

    public static function generateKey($length = 32)
    {
        return base64_encode(openssl_random_pseudo_bytes($length));
    }

    public static function encrypt($data, $key, $method = self::ENCRYPTION_METHOD)
    {
        $encryptionKey = base64_decode($key);
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($method));
        $encrypted = openssl_encrypt($data, $method, $encryptionKey, 0, $iv);
        return base64_encode($encrypted . '::' . $iv);
    }

    public static function decrypt($data, $key, $method = self::ENCRYPTION_METHOD)
    {
        $encryptionKey = base64_decode($key);
        list($encryptedData, $iv) = explode('::', base64_decode($data), 2);
        return openssl_decrypt($encryptedData, $method, $encryptionKey, 0, $iv);
    }

}