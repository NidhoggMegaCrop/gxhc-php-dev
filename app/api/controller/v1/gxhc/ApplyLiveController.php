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

use app\services\gxhc\ApplyLiveServices;
use app\Request;

class ApplyLiveController
{
    protected $services = NULL;

    /**
     * ApplyLiveController constructor.
     * @param ApplyLiveServices $services
     */
    public function __construct(ApplyLiveServices $services)
    {
        $this->services = $services;
    }

    /**
     * 申请直播
     * @param Request $request
     * @return mixed
     */
    public function apply(Request $request)
    {
        $postData = $request->postMore([
            ["name", ""],
            ["project_name", ""],
            ["agree_public", ""],
            ["expected_date", ""],
            ["expected_time", ""],
            ["accept_adjust", ""],
            ["contact", ""],
        ]);
        $postData['uid'] = (int)$request->uid();
        // 检查是否已存在未处理的申请
        if ($this->services->checkExistingApplication($postData['uid'])) {
            return app('json')->fail('请勿重复申请');
        }
        $this->services->apply($postData);

        // 触发飞书通知事件
        event('feishu_apply_live', [
            [
                'applicant' => $postData['name'],
                'project_name' => $postData['project_name'],
                'expected_date' => $postData['expected_date'],
                'expected_time' => $postData['expected_time'],
                'apply_time' => date('Y-m-d H:i:s'),
                'contact' => $postData['contact']
            ],
            'feishu_apply_live'
        ]);

        return app('json')->success('申请成功');
    }

    /**
     * 取消直播申请
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function cancel(Request $request, $id)
    {
        $uid = (int)$request->uid();
        $this->services->cancel($id, $uid);
        return app('json')->success('取消成功');
    }

    /**
     * 获取已预订的时间槽
     * @param Request $request
     * @return mixed
     */
    public function bookedSlots(Request $request)
    {
        $date = $request->param('date', '');
        if (empty($date)) {
            return app('json')->fail('请选择日期');
        }

        $slots = $this->services->getBookedSlots($date);
        return app('json')->success($slots);
    }

    /**
     * 查询用户的最新直播
     * @param Request $request
     * @return mixed
     */
    public function userLive(Request $request)
    {
        $uid = (int)$request->uid();
        $info = $this->services->getUserLatestLive($uid);
        if (is_null($info)) {
            return app('json')->success('', null);
        }
        return app('json')->success($info);
    }
}
