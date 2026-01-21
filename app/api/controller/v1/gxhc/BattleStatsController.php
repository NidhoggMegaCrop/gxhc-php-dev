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

use app\services\gxhc\BattleStatsServices;
use app\Request;

/**
 * 实战战报统计API
 * Class BattleStatsController
 * @package app\api\controller\v1\gxhc
 */
class BattleStatsController
{
    protected $services = NULL;

    /**
     * BattleStatsController constructor.
     * @param BattleStatsServices $services
     */
    public function __construct(BattleStatsServices $services)
    {
        $this->services = $services;
    }

    /**
     * 获取所有启用的战报统计数据
     * @param Request $request
     * @return mixed
     */
    public function getStats(Request $request)
    {
        try {
            $list = $this->services->getAllStats();

            // 格式化数据，方便前端使用
            $data = [];
            foreach ($list as $item) {
                $data[] = [
                    'id' => $item['id'],
                    'key' => $item['key'],
                    'name' => $item['name'],
                    'value' => $item['value'],
                    'unit' => $item['unit'],
                    'description' => $item['description'],
                    'icon' => $item['icon'],
                    'sort' => $item['sort']
                ];
            }

            return app('json')->success($data);
        } catch (\Exception $e) {
            return app('json')->fail($e->getMessage());
        }
    }

    /**
     * 根据key获取单个统计项
     * @param Request $request
     * @return mixed
     */
    public function getStatByKey(Request $request)
    {
        $key = $request->param('key', '');

        if (empty($key)) {
            return app('json')->fail('参数错误');
        }

        try {
            $data = $this->services->getByKey($key);
            return app('json')->success($data);
        } catch (\Exception $e) {
            return app('json')->fail($e->getMessage());
        }
    }
}
