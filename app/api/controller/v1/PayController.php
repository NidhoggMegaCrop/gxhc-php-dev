<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2023 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\api\controller\v1;


use app\Request;
use crmeb\services\app\MiniProgramService;
use crmeb\services\pay\Pay;
use app\common\Log_;

/**
 * 支付回调
 * Class PayController
 * @package app\api\controller\v1
 */
class PayController
{
    public function setXmlLog($type)
    {
        //存储微信的回调
        $xml = PHP_VERSION <= 5.6 ? $GLOBALS['HTTP_RAW_POST_DATA'] : file_get_contents('php://input');
        if (empty($xml)) {
            echo "error";
            exit;
        }
        // 修改后
        $log_name = public_path() . '/' . $type . '_notify_url.log'; //log文件路径
        Log_::log_result($log_name, __LINE__ . "【微信接收到的notify通知】:\n" . $xml . "\r\n");
        //将XML转为array        
        $dxml = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        Log_::log_result($log_name, __LINE__ . "=====" . json_encode($dxml) . "\r\n");
    }
    /**
     * 支付回调
     * @param string $type
     * @return string|void
     * @throws \EasyWeChat\Core\Exceptions\FaultException
     */
    public function notify(string $type)
    {
        switch (urldecode($type)) {
            case 'alipay':
                /** @var Pay $pay */
                $pay = app()->make(Pay::class, ['ali_pay']);
                return $pay->handleNotify();
            case 'v3wechat':
                return app()->make(Pay::class, ['v3_wechat_pay'])->handleNotify()->getContent();
            case 'routine':
                $this->setXmlLog($type);
                return MiniProgramService::handleNotify();
            case 'wechat':
                if (sys_config('pay_wechat_type')) {
                    /** @var Pay $pay */
                    $pay = app()->make(Pay::class, ['v3_wechat_pay']);
                } else {
                    /** @var Pay $pay */
                    $pay = app()->make(Pay::class);
                }
                return $pay->handleNotify()->getContent();
            default:
                if (strstr($type, 'allin') !== false) {
                    /** @var Pay $pay */
                    $pay = app()->make(Pay::class, ['allin_pay']);
                    return $pay->handleNotify($type);
                }
        }
    }

    /**
     * 支付配置
     * @param Request $request
     * @return mixed
     */
    public function config(Request $request)
    {
        $config = [
            [
                'icon' => 'icon-weixinzhifu',
                'name' => '微信支付',
                'value' => 'weixin',
                'title' => '使用微信快捷支付',
                'number' => null,
                'payStatus' => !!sys_config('pay_weixin_open', 0),
            ],
            [
                'icon' => 'icon-zhifubao',
                'name' => '支付宝支付',
                'value' => 'alipay',
                'title' => '使用线上支付宝支付',
                'number' => null,
                'payStatus' => !!sys_config('ali_pay_status', 0),
            ],
            [
                'icon' => 'icon-yuezhifu',
                'name' => '余额支付',
                'value' => 'yue',
                'title' => '当前可用余额',
                'number' => $request->user('now_money'),
                'payStatus' => (int)sys_config('yue_pay_status', 0) === 1,
            ],
            [
                'icon' => 'icon-yuezhifu1',
                'name' => '线下支付',
                'value' => 'offline',
                'title' => '选择线下付款方式',
                'number' => null,
                'payStatus' => (int)sys_config('offline_pay_status', 0) === 1,
            ],
            [
                'icon' => 'icon-haoyoudaizhifu',
                'name' => '好友代付',
                'value' => 'friend',
                'title' => '找微信好友支付',
                'number' => null,
                'payStatus' => !!sys_config('friend_pay_status', 0),
            ]
        ];

        return app('json')->success($config);
    }
}
