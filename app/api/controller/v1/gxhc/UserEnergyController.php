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

class UserEnergyController
{
    protected $services = NUll;

    /**
     * UserEnergyServices constructor.
     * @param UserEnergyServices $services
     */
    public function __construct(UserEnergyServices $services)
    {
        $this->services = $services;
    }

    public function userEnergy(Request $request)
    {
        $uid = (int)$request->uid();
        $userServices = app()->make(UserServices::class);
        $energy = $userServices->value(['uid' => $uid], 'energy');
        $shareRecordServices = app()->make(ShareRecordServices::class);
        $shareIds = $shareRecordServices->getInviteColumnByUid($uid, 0);
        $spredadUids = $shareRecordServices->getInviteColumnByUid($uid, 1);
        $list = [];
        foreach ($spredadUids as $spredadUid) {
            $list[] = $userServices->getOne(['uid' => $spredadUid], '*');
        }

        return app('json')->success([
            'energy' => $energy,
            "spredadUids" => count($spredadUids),
            "shareCount" => count($shareIds),
            "spredadUidsList" => $list
        ]);
    }

    public function energyList(Request $request)
    {
        $uid = (int)$request->uid();
        $data = $this->services->getIntegralList($uid);
        return app('json')->success($data);
    }
}
