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
     * [WeixinJSBridge] Generate js config for payment.
     *
     * <pre>
     * WeixinJSBridge.invoke(
     *  'getBrandWCPayRequest',
     *  ...
     * );
     * </pre>
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
     * [JSSDK] Generate js config for payment.
     *
     * <pre>
     * wx.chooseWXPay({...});
     * </pre>
     */
    public function sdkConfig(string $prepayId): array
    {
        return $this->bridgeConfig($prepayId, false);
    }

    /**
     * Generate app payment parameters.
     */
    public function appConfig(string $tokenId): array
    {
        $params = [
            'appId' => $this->app['config']->app_id,
            'bargainorId' => $this->app['config']->mch_id,
            'tokenId' => $tokenId,
            'nonce' => uniqid('', false),
            'timeStamp' => time(),
            'sigType' => 'HMAC-SHA1',
            'pubAcc' => '',
        ];

        $params['sig'] = Utils::generateSign($params, $this->app['config']->key, 'sha1');

        return $params;
    }
}
