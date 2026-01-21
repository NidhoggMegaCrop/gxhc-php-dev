<?php

namespace app\api\controller\v1;


use app\services\activity\live\LiveGoodsServices;
use app\services\activity\live\LiveRoomServices;
use app\services\agent\AgentManageServices;
use app\services\order\StoreOrderServices;
use app\services\order\StoreOrderTakeServices;
use app\services\product\product\StoreProductServices;
use app\services\system\attachment\SystemAttachmentServices;
use app\services\system\crontab\SystemCrontabServices;

/**
 * 定时任务控制器
 * @author 吴汐
 * @email 442384644@qq.com
 * @date 2023/02/21
 */
class CrontabController
{
    /**
     * 定时任务调用接口
     * @author 吴汐
     * @email 442384644@qq.com
     * @date 2023/02/17
     */
    public function crontabRun()
    {
        app()->make(SystemCrontabServices::class)->crontabRun();
    }

    /**
     * 检测定时任务是否正常，必须6秒执行一次
     */
    public function crontabCheck()
    {
        file_put_contents(root_path() . 'runtime/.timer', time());
    }

    public function test()
    {
        //推送消息
        event('NoticeListener', [['spreadUid' => 3, 'user_type' => '', 'nickname' => '你好'], 'bind_spread_uid']);
    }
}
