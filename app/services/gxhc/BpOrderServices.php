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
declare(strict_types=1);

namespace app\services\gxhc;

use app\services\BaseServices;
use app\services\user\UserServices;
use app\services\user\UserLabelRelationServices;
use app\dao\gxhc\BpOrderDao;
use crmeb\exceptions\AdminException;
use crmeb\services\FormBuilder as Form;
use think\facade\Route as Url;
use app\services\wechat\WechatUserServices;
use crmeb\exceptions\ApiException;
use app\services\pay\PayServices;
use crmeb\utils\Str;
use think\exception\ValidateException;
use app\services\pay\OrderPayServices;


/**
 *
 * Class BpOrderServices
 * @package app\services\BpOrder
 */
class BpOrderServices extends BaseServices
{

    /**
     * BpOrderServices constructor.
     * @param BpOrderrDao $dao
     */
    public function __construct(BpOrderDao $dao)
    {
        $this->dao = $dao;
    }

    public function orderUnpaidCancel()
    {
        echo 2;
    }

    public function createOrder(array $data)
    {
        // 验证必要参数
        if (!isset($data['uid']) || !isset($data['pay_price'])) {
            throw new \think\Exception('缺少必要参数');
        }

        if (!isset($data['run_id'])) {
            throw new \think\Exception('缺少必要参数');
        }

        if ($data['use_energy'] && $data['energy_amount'] > 0) {
            $userServices = app()->make(UserServices::class);
            $userInfo = $userServices->getOne(['uid' => $data['uid']]);
            if ($userInfo['energy'] < $data['energy_amount']) {
                throw new AdminException('用户能量不足');
            }
        }

        $total_price = $data['pay_price'] + $data['energy_amount'];
        // 构造订单数据
        $orderData = [
            'uid' => $data['uid'],
            'pay_uid' => $data['uid'],
            'run_id' => $data['run_id'],
            'pay_price' => $data['pay_price'],
            'total_price' => $total_price,
            'deduction_price' => $data['energy_amount'],
            'total_num' => 1,
            'order_id' => $this->generateOrderSn(), // 生成订单号
            'status' => 0, // 默认未支付状态
            'add_time' => time(), // 创建时间
        ];

        // 使用DAO创建订单
        $order = $this->dao->save($orderData);

        if (!$order) {
            throw new ApiException('订单创建失败');
        }

        // 获取并返回订单ID
        return $this->dao->value(['order_id' => $orderData['order_id']], 'id');
    }

    /**
     * 生成订单号
     * @return string
     */
    private function generateOrderSn()
    {
        return 'BP' . date('YmdHis') . rand(1000, 9999);
    }

    public function payOrder($orderInfo)
    {
        $paytype = 'weixin';
        $quitUrl = '';
        $order = $this->dao->get(['order_id' => $orderInfo['order_id']]);
        $payServices = app()->make(OrderPayServices::class);
        return $payServices->beforePay($order->toArray(), $paytype, ['quitUrl' => $quitUrl]);
    }

    public function get_record_list($where)
    {
        $page = (int)($where['page'] ?? 1);
        $limit = (int)($where['limit'] ?? 10);
        $status = $where['status'] ?? '';
        $keyword = $where['keyword'] ?? '';
        $date = $where['date'] ?? '';
        $query = [];
        if ($status !== '') {
            $query['status'] = $status;
        }
        if ($keyword !== '') {
            $query['name|project_name'] = ['like', '%' . $keyword . '%'];
        }
        if ($date !== '') {
            $query['expected_date'] = $date;
        }
        $userService = app()->make(UserServices::class);
        $bpOrderService = app()->make(BpOrderServices::class);
        $userLabelRelationServices = app()->make(UserLabelRelationServices::class);
        $list = $this->dao->getRecordList($query, $page, $limit);
        foreach ($list as &$item) {
            $userInfo = $userService->getUserInfo($item['uid'], 'nickname,phone,spread_uid');
            $item['nickname'] = $userInfo['nickname'];
            $item['phone'] = $userInfo['phone'];
            $item['spread_uid'] = $userInfo['spread_uid'];
            $item['spread_tag'] = '';
            if ($userInfo['spread_uid']) {
                $spreadInfo = $userService->getOne(['uid' => $userInfo['spread_uid']], 'nickname');
                $item['spread_nickname'] = !empty($spreadInfo['nickname']) ? $spreadInfo['nickname'] : '';
                $userTags = $userLabelRelationServices->getUserLabelList(['uid' => $userInfo['spread_uid']]);
                $item['spread_userTag'] = !empty($userTags) ? $userTags : '';
            }
            $order_info = $bpOrderService->getOne([
                'order_id' => $item['order_id'],
                'status' => 'SUCCEEDED'
            ], 'pay_price,order_id');
            $item['price'] = !empty($order_info['pay_price']) ? $order_info['pay_price'] : '';
        }
        $count = $this->dao->count($query);
        return compact('list', 'count');
    }
}
