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

use app\services\gxhc\ShareRecordServices;
use app\services\gxhc\UserEnergyServices;
use app\services\user\UserServices;
use crmeb\services\CacheService;
use app\Request;

class ShareController
{
    protected $services = NUll;

    /**
     * UserEnergyController constructor.
     * @param UserAuthServices $services
     */
    public function __construct(ShareRecordServices $services)
    {
        $this->services = $services;
    }

    public function get_share(Request $request)
    {
        $postData = $request->postMore([
            ["spread", 0],
        ]);

        // 获取当前用户信息
        $uid = $request->uid();
        if (!$uid) {
            return app('json')->fail('用户未登录');
        }
        $invite_uid = $postData['spread'];
        $userService = app()->make(UserServices::class);
        $spread_user = $userService->getUserInfo($invite_uid);
        if (!$invite_uid || !$spread_user) {
            return app('json')->fail('邀请人不存在');
        }
        $currer_user = $userService->getUserInfo($uid);
        if (empty($currer_user['spread_uid'])) {
            $userService->update($uid, ['spread_uid' => $invite_uid], 'uid');
            /*
            $isNew = $this->services->getInviteCountByUid($invite_uid, 1, 2);
            if ($isNew) {
                return app('json')->fail('您已邀请过用户');
            }
            // 判断当前用户已经邀请的人数
            $inviteCount = $this->services->getInviteCountByUid($invite_uid, 1, 1);
            if ($inviteCount >= 5) {
                // 修改状态为1的纪录为2
                $this->services->updateRecordState($invite_uid,1);
                // 299元积分奖励
                $userEnergyServices = app()->make(UserEnergyServices::class);
                $exp_num = 299;
                if ($exp_num) {
                    $userService->bcInc($invite_uid, 'energy', $exp_num, 'uid');
                    $now_energy = $spread_user['energy'] + $exp_num;
                    $userEnergyServices->income('get_user_energy', $invite_uid, $exp_num, $now_energy, 1);
                }
                return app('json')->fail('您已达到邀请上限');
            }
            $shareData = [
                'uid' => $uid,
                'invite_uid' => $invite_uid,
                'is_new' => 1,
                'add_time' => time()
            ];
            $result = $this->services->saveRecord($shareData);
            */
            return app('json')->success('成功');
        }
        return app('json')->success('用户已绑定分享人');
    }

    public function share_set(Request $request)
    {
        $postData = $request->postMore([
            ["spread", 0],
        ]);

        // 获取当前用户信息
        $invite_uid = $postData['spread'];
        $userService = app()->make(UserServices::class);
        $spread_user = $userService->getUserInfo($invite_uid);
        if (!$invite_uid || !$spread_user) {
            return app('json')->fail('邀请人不存在');
        }
        $isNew = $this->services->getInviteCountByUid($invite_uid, 0, 2);
        if ($isNew) {
            return app('json')->fail('您已邀请过用户');
        }
        // 判断当前用户已经邀请的人数
        $inviteCount = $this->services->getInviteCountByUid($invite_uid, 0, 1);
        // var_dump($inviteCount);
        if ($inviteCount >= 4) {
            // 修改状态为1的纪录为2
            $this->services->updateRecordState($invite_uid);
            // 299元85折积分奖励
            $userEnergyServices = app()->make(UserEnergyServices::class);
            $exp_num = 299 - (int)(299 * 0.85);
            if ($exp_num) {
                $userService->bcInc($invite_uid, 'energy', $exp_num, 'uid');
                $now_energy = $spread_user['energy'] + $exp_num;
                $userEnergyServices->income('get_user_share', $invite_uid, $exp_num, $now_energy, 1);
            }
            // var_dump($exp_num);
            return app('json')->fail('您已达到邀请上限');
        }

        // 保存分享记录
        $shareData = [
            'invite_uid' => $invite_uid,
            'add_time' => time()
        ];

        try {
            $result = $this->services->saveRecord($shareData);
            if ($result) {
                return app('json')->success('邀请成功');
            } else {
                return app('json')->fail('邀请失败');
            }
        } catch (\Exception $e) {
            return app('json')->fail('保存分享记录时发生错误：' . $e->getMessage());
        }
    }
}
