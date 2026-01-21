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

namespace app\services\gxhc;


use app\dao\gxhc\BpOrderDao;
use app\services\activity\lottery\LuckLotteryServices;
use app\services\activity\combination\StorePinkServices;
use app\services\user\UserServices;
use app\services\gxhc\UserEnergyServices;
use app\services\gxhc\ShareRecordServices;
use app\services\BaseServices;
use app\services\pay\PayServices;
use crmeb\exceptions\ApiException;
use crmeb\exceptions\AdminException;
use app\services\gxhc\BpRecordServices;
use app\common\BpAgent;

/**
 * Class BpOrderSuccessServices
 * @package app\services\order
 * @method getOne(array $where, ?string $field = '*', array $with = []) 获取去一条数据
 */
class BpOrderSuccessServices extends BaseServices
{
    /**
     *
     * BpOrderSuccessServices constructor.
     * @param BpOrderDao $dao
     */
    public function __construct(BpOrderDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 0元支付
     * @param array $orderInfo
     * @param int $uid
     * @return bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function zeroYuanPayment($orderInfo, $uid, $payType = PayServices::YUE_PAY)
    {
        // if ($orderInfo['paid']) {
        //     throw new ApiException(410265);
        // }
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        /** @var UserBillServices $userBillServices */
        $userEnergyServices = app()->make(UserEnergyServices::class);
        if ($orderInfo['deduction_price'] > 0) {
            $userInfo = $userServices->getOne(['uid' => $orderInfo['uid']]);
            if ($userInfo['energy'] < $orderInfo['energy_amount']) {
                throw new AdminException('用户能量不足');
            }
        }
        $userServices->bcDec($orderInfo['uid'], 'energy', $orderInfo['deduction_price'], 'uid');
        $now_money = $userServices->value(['uid' => $orderInfo['uid']], 'now_money');
        $res4 = $userEnergyServices->income('deduction', $orderInfo['uid'], [
            'number' => floatval($orderInfo['total_price'])
        ], $now_money, $orderInfo['id']);
        return $this->paySuccess($orderInfo, $payType); //余额支付成功
    }

    /**
     * 支付成功
     * @param array $orderInfo
     * @param string $paytype
     * @param array $other
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function paySuccess($orderInfo, $paytype = PayServices::WEIXIN_PAY, array $other = [])
    {
        $updata = ['paid' => 1, 'pay_type' => $paytype, 'pay_time' => time()];
        $orderInfo['pay_time'] = $updata['pay_time'];
        $orderInfo['pay_type'] = $paytype;
        if ($other && isset($other['trade_no'])) {
            $updata['trade_no'] = $other['trade_no'];
        }
        $res1 = $this->dao->update($orderInfo['id'], $updata);
        $resPink = true;
        $orderInfo['send_name'] = $orderInfo['real_name'];
        //订单支付成功后置事件
        // event('OrderPaySuccessListener', [$orderInfo]);
        //用户推送消息事件
        // event('NoticeListener', [$orderInfo, 'order_pay_success']);
        //支付成功给客服发送消息
        // event('NoticeListener', [$orderInfo, 'admin_pay_success_code']);
        // 推送订单
        // event('OutPushListener', ['order_pay_push', ['order_id' => (int)$orderInfo['id']]]);

        //自定义消息-订单支付成功
        $orderInfo['time'] = date('Y-m-d H:i:s');
        $orderInfo['phone'] = $orderInfo['user_phone'];
        // event('CustomNoticeListener', [$orderInfo['uid'], $orderInfo, 'order_pay_success']);


        $pipeline = 'bp_diagnosis';
        $target = 'export_optimization';
        $runId = $orderInfo['run_id'];
        // 调用公共静态类处理API请求
        $result = BpAgent::runBpWithoutFile(
            $pipeline,
            $target,
            $runId
        );
        // var_dump($result,$orderInfo['run_id'],$orderInfo);
        if ($result['code'] != 200) {
            throw new AdminException('深度优化失败～');
        }
        $bpRecordServices = app()->make(BpRecordServices::class);
        $re_id = $bpRecordServices->value(['order_id' => $orderInfo['order_id']], 'id');
        if (!$re_id) {
            $bpAInfo = $bpRecordServices->getOne(['run_id' => $runId], 'origina_filename');
            $status = !empty($result['data']['status']) ? $result['data']['status'] : 'QUEUED';
            $run_response = !empty($result['data']) ? $result['data'] : $result;
            $bpRecordServices->saveBpRecord($orderInfo['uid'], [
                'run_id' => $runId,
                'order_id' => $orderInfo['order_id'],
                'pipeline' => $pipeline,
                'target' => $target,
                'is_create' => $result['code'] == 200 ? 1 : 0,
                'status' => $status,
                'origina_filename' => $bpAInfo['origina_filename'],
                'filename' => $bpAInfo['origina_filename'],
                'add_time' => time(),
                'run_response' => json_encode($run_response)
            ]);
            // dumpSql();
        }

        //自定义事件-订单支付
        // event('CustomEventListener', ['order_pay', [
        //     'uid' => $orderInfo['uid'],
        //     'id' => (int)$orderInfo['id'],
        //     'order_id' => $orderInfo['order_id'],
        //     'real_name' => $orderInfo['real_name'],
        //     'user_phone' => $orderInfo['user_phone'],
        //     'user_address' => $orderInfo['user_address'],
        //     'total_num' => $orderInfo['total_num'],
        //     'pay_price' => $orderInfo['pay_price'],
        //     'pay_postage' => $orderInfo['pay_postage'],
        //     'deduction_price' => $orderInfo['deduction_price'],
        //     'coupon_price' => $orderInfo['coupon_price'],
        //     'store_name' => $orderInfo['storeName'],
        //     'add_time' => date('Y-m-d H:i:s', $orderInfo['add_time']),
        // ]]);

        $res = $res1 && $resPink;
        return false !== $res;
    }
}
