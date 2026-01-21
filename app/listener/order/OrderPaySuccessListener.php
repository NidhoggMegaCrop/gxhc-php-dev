<?php


namespace app\listener\order;


use app\jobs\AgentJob;
use app\jobs\OrderJob;
use app\jobs\ProductLogJob;


use app\services\order\StoreOrderCartInfoServices;
use app\services\order\StoreOrderDeliveryServices;
use app\services\order\StoreOrderInvoiceServices;
use app\services\order\StoreOrderServices;
use app\services\order\StoreOrderStatusServices;
use app\services\pay\PayServices;
use app\services\product\product\StoreProductCouponServices;
use app\services\product\sku\StoreProductAttrValueServices;
use app\services\product\sku\StoreProductVirtualServices;
use app\services\message\MessageSystemServices;

use app\services\user\UserServices;
use crmeb\exceptions\AdminException;
use crmeb\interfaces\ListenerInterface;
use think\facade\Log;

/**
 * 订单支付成功后
 * Class OrderPaySuccessListener
 * @package app\listener\order
 */
class OrderPaySuccessListener implements ListenerInterface
{
    public function handle($event): void
    {
        [$orderInfo] = $event;

        //修改开票数据支付状态
        $orderInvoiceServices = app()->make(StoreOrderInvoiceServices::class);
        $orderInvoiceServices->update(['order_id' => $orderInfo['id']], ['is_pay' => 1]);

        //支付成功后发送消息
        OrderJob::dispatch([$orderInfo]);

        //商品日志记录支付记录
        ProductLogJob::dispatch(['pay', ['uid' => $orderInfo['uid'], 'order_id' => $orderInfo['id']]]);
    }
}
