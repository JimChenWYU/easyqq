<?php

namespace EasyQQ\Kernel\Support;

use Closure;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class Utils
 *
 * @author JimChen <imjimchen@163.com>
 */
class Utils
{
    /**
     * @param array  $attributes
     * @param string $key
     * @param string $encryptMethod
     * @return string
     */
    public static function generateSign(array $attributes, $key, $encryptMethod = 'md5')
    {
        ksort($attributes);

        $attributes['key'] = $key;

        return strtoupper(call_user_func_array($encryptMethod, [urldecode(http_build_query($attributes))]));
    }

    /**
     * @param string $signType
     * @param string $secretKey
     * @return Closure|string
     */
    public static function getEncryptMethod(string $signType, string $secretKey = '')
    {
        if ('HMAC-SHA256' === $signType) {
            return function ($str) use ($secretKey) {
                return hash_hmac('sha256', $str, $secretKey);
            };
        }

        return 'md5';
    }

    /**
     * @return mixed|string
     */
    public static function getClientIp()
    {
        if (php_sapi_name() === 'cli') {
            // for php-cli(phpunit etc.)
            $ip = defined('PHPUNIT_RUNNING') ? '127.0.0.1' : gethostbyname(gethostname());
        } else {
            $ip = Request::createFromGlobals()->getClientIp();
        }

        return filter_var($ip, FILTER_VALIDATE_IP) ?: '127.0.0.1';
    }

    /**
     * @return mixed|string
     */
    public static function getServerIp()
    {
        if (php_sapi_name() === 'cli') {
            // for php-cli(phpunit etc.)
            $ip = defined('PHPUNIT_RUNNING') ? '127.0.0.1' : gethostbyname(gethostname());
        } else {
            $ip = Request::createFromGlobals()->getHost();
        }

        return filter_var($ip, FILTER_VALIDATE_IP) ?: '127.0.0.1';
    }

    /**
     * Return current url.
     *
     * @return string
     */
    public static function currentUrl()
    {
        return Request::createFromGlobals()->getSchemeAndHttpHost();
    }

    /**
     * @param string $content
     * @param string $publicKey
     * @return string
     */
    public static function rsaPublicEncrypt($content, $publicKey)
    {
        $encrypted = '';
        openssl_public_encrypt($content, $encrypted, openssl_pkey_get_public($publicKey), OPENSSL_PKCS1_OAEP_PADDING);

        return base64_encode($encrypted);
    }
}
