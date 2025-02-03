<?php

namespace Icinga\Module\Grafana\Helpers;

use OpenSSLAsymmetricKey;
use InvalidArgumentException;
use RuntimeException;

class JwtToken
{
    const RSA_KEY_BITS = 2048;
    const JWT_PRIVATEKEY_FILE = '/etc/icingaweb2/modules/grafana/jwt.key.priv';
    const JWT_PUBLICKEY_FILE = '/etc/icingaweb2/modules/grafana/jwt.key.pub';

    /**
     * Create JWT Token
     */
    public static function create(string $sub, int $exp = 0, string $iss = null, array $claims = null): string
    {
        $privateKeyFile = JwtToken::JWT_PRIVATEKEY_FILE;

        $privateKey = openssl_pkey_get_private(
            file_get_contents($privateKeyFile),
        );

        $payload = [
            'sub' => $sub,
            'iat' => time(),
            'nbf' => time(),
        ];

        if (isset($claims)) {
            $payload = array_merge($payload, $claims);
        }

        if (!empty($iss)) {
            $payload['iss'] = $iss;
        }

        return JwtToken::encode($payload, $privateKey, 'RS256', $exp);
    }

    /**
     * Generate Private and Public RSA Keys
     */
    public static function generateRsaKeys()
    {
        $ret = file_exists(JwtToken::JWT_PRIVATEKEY_FILE);
        if ($ret) {
            return;
        }

        $config = array(
            "private_key_bits" => JwtToken::RSA_KEY_BITS,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        );

        $res = openssl_pkey_new($config);
        openssl_pkey_export($res, $privKey);
        $pubKey = openssl_pkey_get_details($res);
        $pubKey = $pubKey["key"];

        file_put_contents(JwtToken::JWT_PRIVATEKEY_FILE, $privKey);
        file_put_contents(JwtToken::JWT_PUBLICKEY_FILE, $pubKey);
    }

    private static function encode(array $payload, OpenSSLAsymmetricKey $privateKey, string $algorithm = 'RS256', int $expiration = 3600): string
    {
        //  Verify that the algorithm is compatible with asymmetric keys
        if ($algorithm !== 'RS256' && $algorithm !== 'RS512') {
            throw new InvalidArgumentException("Unsupported algorithm for assymmetric keys: $algorithm");
        }

        // Define the JWT header
        $header = json_encode([
            'alg' => $algorithm,
            'typ' => 'JWT'
        ]);

        // Add expiration time to the payload
        if ($expiration > 0) {
            $payload['exp'] = time() + $expiration;
        }

        // Encode header and payload to base64 URL
        $base64Header = JwtToken::base64UrlEncode($header);
        $base64Payload = JwtToken::base64UrlEncode(json_encode($payload));

        // Create the signature
        $dataToSign = "$base64Header.$base64Payload";
        $signature = '';
        $success = openssl_sign($dataToSign, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        if (!$success) {
            throw new RuntimeException("Failed to sign the JWT with the private key.");
        }

        // Encode signature to base64 URL
        $base64Signature = JwtToken::base64UrlEncode($signature);

        // Return the complete token
        return "$base64Header.$base64Payload.$base64Signature";
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
