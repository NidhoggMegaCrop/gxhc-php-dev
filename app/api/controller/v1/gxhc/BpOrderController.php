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

namespace app\api\controller\v1\gxhc;

use app\services\gxhc\BpOrderServices;
use app\services\gxhc\BpOrderSuccessServices;
use crmeb\services\CacheService;
use app\Request;

class BpOrderController
{
    protected $services = NUll;

    /**
     * BpOrderController constructor.
     * @param BpOrderServices $services
     */
    public function __construct(BpOrderServices $services)
    {
        $this->services = $services;
    }

    public function createOrder(Request $request)
    {
        $postData = $request->postMore([
            ['pay_price', 299],
            ['use_energy', false],
            ['energy_amount', 0],
            ['run_id', ''],
        ]);
        $postData['uid'] = (int)$request->uid();
        // $postData['pay_price'] = 0.01;

        // 调用服务层创建订单
        $order_id = $this->services->createOrder($postData);

        if ($order_id) {
            return app('json')->success('订单创建成功', ['order_id' => $order_id]);
        } else {
            return app('json')->fail('订单创建失败');
        }
    }

    public function pay(Request $request)
    {
        $postData = $request->postMore([
            ['order_id', ''],
        ]);

        $uid = (int)$request->uid();

        // 验证参数
        if (empty($postData['order_id'])) {
            return app('json')->fail('订单ID不能为空');
        }

        // 查询订单信息
        $orderInfo = $this->services->get(['id' => $postData['order_id']]);
        if (!$orderInfo) {
            return app('json')->fail('订单不存在');
        }

        if ($orderInfo->is_del == 1 || $orderInfo->is_system_del == 1) {
            return app('json')->fail('订单已经超过系统支付时间，无法支付，请重新下单');
        }

        $orderInfo->is_channel = $this->getChannel[$request->getFromType()] ?? ($request->isApp() ? 0 : 1);
        $orderInfo->pay_uid = $request->uid();

        try {
            \think\facade\Db::startTrans(); // 开启事务

            $orderInfo->save(); // 保存订单状态

            //0元支付
            if (bcsub((string)$orderInfo['pay_price'], '0', 2) <= 0) {
                /** @var BpOrderSuccessServices $success */
                $success = app()->make(BpOrderSuccessServices::class);
                $payPriceStatus = $success->zeroYuanPayment($orderInfo, $uid);
                if ($payPriceStatus) { //0元支付成功
                    \think\facade\Db::commit(); // 提交事务
                    return app('json')->status('success', '支付成功', ['order_id' => $orderInfo['order_id'], 'key' => $orderInfo['unique']]);
                } else {
                    \think\facade\Db::rollback(); // 回滚事务
                    return app('json')->status('pay_error', 410216);
                }
            }

            // 调用服务层处理支付
            $payParams = $this->services->payOrder($orderInfo);

            if ($payParams) {
                \think\facade\Db::commit(); // 提交事务
                return app('json')->success('获取支付参数成功', $payParams);
            } else {
                \think\facade\Db::rollback(); // 回滚事务
                return app('json')->fail('获取支付参数失败');
            }
        } catch (\Exception $e) {
            \think\facade\Db::rollback(); // 异常时回滚事务
            return app('json')->fail('支付处理失败：' . $e->getMessage());
        }
    }
}
