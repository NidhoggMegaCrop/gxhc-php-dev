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

use app\services\gxhc\RedemptionCodeServices;
use app\model\gxhc\RedemptionCode;
use app\services\user\UserServices;
use app\Request;

/**
 * 兑换码控制器（用户端）
 * Class RedemptionCodeController
 * @package app\api\controller\v1\gxhc
 */
class RedemptionCodeController
{
    /**
     * @var RedemptionCodeServices
     */
    protected $services = null;

    /**
     * RedemptionCodeController constructor.
     * @param RedemptionCodeServices $services
     */
    public function __construct(RedemptionCodeServices $services)
    {
        $this->services = $services;
    }

    /**
     * 用户铸造兑换码
     * @param Request $request
     * @return mixed
     */
    public function mint(Request $request)
    {
        $uid = (int)$request->uid();
        $data = $request->postMore([
            ['denomination', 0]
        ]);

        $energyValue = (int)$data['denomination'];
        if ($energyValue <= 0) {
            return app('json')->fail('请输入兑换数量');
        }

        try {
            $result = $this->services->userMintCode($uid, $energyValue);
            return app('json')->success($result);
        } catch (\Exception $e) {
            return app('json')->fail($e->getMessage());
        }
    }

    /**
     * 核销兑换码
     * @param Request $request
     * @return mixed
     */
    public function redeem(Request $request)
    {
        $uid = (int)$request->uid();
        $data = $request->postMore([
            ['code', '']
        ]);

        $code = trim($data['code']);
        if (empty($code)) {
            return app('json')->fail('请输入兑换码');
        }

        try {
            $result = $this->services->redeemCode($uid, $code);
            return app('json')->success([
                'energy_value' => $result['energy_value'],
                'new_balance' => $result['new_balance'],
                'message' => '兑换成功，获得' . $result['energy_value'] . '能量'
            ]);
        } catch (\Exception $e) {
            return app('json')->fail($e->getMessage());
        }
    }

    /**
     * 获取用户铸造的兑换码列表
     * @param Request $request
     * @return mixed
     */
    public function myMintedCodes(Request $request)
    {
        $uid = (int)$request->uid();
        $data = $request->postMore([
            ['page', 1],
            ['limit', 10]
        ]);

        $page = (int)$data['page'];
        $limit = (int)$data['limit'];

        try {
            $result = $this->services->getUserMintedCodes($uid, $page, $limit);
            return app('json')->success($result);
        } catch (\Exception $e) {
            return app('json')->fail($e->getMessage());
        }
    }

    /**
     * 获取用户兑换码列表
     * @param Request $request
     * @return mixed
     */
    public function myMintedUseCodes(Request $request)
    {
        $uid = (int)$request->uid();
        $data = $request->postMore([
            ['page', 1],
            ['limit', 10]
        ]);

        $page = (int)$data['page'];
        $limit = (int)$data['limit'];

        try {
            $result = $this->services->getUserUseCode($uid, $page, $limit);
            return app('json')->success($result);
        } catch (\Exception $e) {
            return app('json')->fail($e->getMessage());
        }
    }

    /**
     * 查询兑换码状态
     * @param Request $request
     * @return mixed
     */
    public function checkCode(Request $request)
    {
        $data = $request->getMore([
            ['code', '']
        ]);

        $code = trim($data['code']);
        if (empty($code)) {
            return app('json')->fail('请输入兑换码');
        }

        try {
            $detail = $this->services->getCodeDetail($code);

            // 只返回必要信息，不暴露敏感数据
            return app('json')->success([
                'energy_value' => $detail['energy_value'],
                'status' => $detail['status'],
                'status_text' => $detail['status_text'],
                'is_valid' => $detail['status'] == 0 // 0表示未使用
            ]);
        } catch (\Exception $e) {
            return app('json')->fail($e->getMessage());
        }
    }

    /**
     * 获取用户能量信息（包含可铸造信息）
     * @param Request $request
     * @return mixed
     */
    public function energyInfo(Request $request)
    {
        $uid = (int)$request->uid();

        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $energy = $userServices->value(['uid' => $uid], 'energy');

        $redemptionCodeServices = app()->make(RedemptionCodeServices::class);
        $count = $redemptionCodeServices->getUserTodayMintedCount($uid);

        // 默认铸造能量值
        $defaultMintValue = 299;
        $canMint = $energy >= $defaultMintValue;

        return app('json')->success([
            'energy' => (int)$energy,
            'todayMintedCount' => RedemptionCode::MAX_MINT_COUNT_PER_DAY - $count,
            'default_mint_value' => $defaultMintValue,
            'can_mint' => $canMint,
            'max_mint_count' => $canMint ? floor($energy / $defaultMintValue) : 0
        ]);
    }
}
