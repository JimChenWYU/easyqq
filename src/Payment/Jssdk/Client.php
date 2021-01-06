<?php

namespace EasyQQ\Payment\Jssdk;

use EasyQQ\Kernel\Support\Utils;
use EasyQQ\Payment\Kernel\BaseClient;

/**
 * Class Client
 *
 * @author JimChen <imjimchen@163.com>
 */
class Client extends BaseClient
{
    /**
     * Generate js config for payment.
     *
     * @return string|array
     */
    public function bridgeConfig(string $tokenId, string $pubAcc, string $pubAccHint = '', bool $json = true)
    {
        $appId = $this->app['config']->sub_appid ?: $this->app['config']->app_id;
        $mchId = $this->app['config']->mch_id;
        $params = [
            'tokenId' => $tokenId,
            'pubAcc' => $pubAcc,
            'pubAccHint' => $pubAccHint,
            'appInfo' => "appid#{$appId}|bargainor_id#{$mchId}|channel#wallet",
        ];

        return $json ? json_encode($params) : $params;
    }

    /**
     * Generate js config for payment.
     */
    public function sdkConfig(string $prepayId): array
    {
        return $this->bridgeConfig($prepayId, false);
    }

    /**
     * Generate app payment parameters.
     */
    public function appConfig(string $tokenId, string $pubAccHint = ''): array
    {
        $params = [
            'appId' => $this->app['config']->app_id,
            'bargainorId' => $this->app['config']->mch_id,
            'tokenId' => $tokenId,
            'nonce' => uniqid('', false),
            'pubAcc' => '',
        ];

        $params['sig'] = Utils::generateSign($params, $this->app['config']->key, 'sha1');

        $params['sigType'] = 'HMAC-SHA1';
        $params['timeStamp'] = time();
        $params['pubAccHint'] = $pubAccHint;

        return $params;
    }
}
