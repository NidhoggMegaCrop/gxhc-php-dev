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

use app\Request;

/**
 * 实战战报统计API（简化版 - 使用系统配置存储）
 * Class BattleStatsController
 * @package app\api\controller\v1\gxhc
 */
class BattleStatsController
{
    /**
     * 配置项名称
     */
    const CONFIG_NAME = 'gxhc_battle_stats';

    /**
     * 获取所有数据
     * @return array
     */
    protected function getAllData(): array
    {
        $data = sys_config(self::CONFIG_NAME);
        if (empty($data) || !is_array($data)) {
            return [];
        }
        return $data;
    }

    /**
     * 获取所有启用的战报统计数据
     * @param Request $request
     * @return mixed
     */
    public function getStats(Request $request)
    {
        $allData = $this->getAllData();

        // 只返回启用的
        $list = array_filter($allData, function($item) {
            return ($item['status'] ?? 0) == 1;
        });

        // 排序
        usort($list, function($a, $b) {
            return ($b['sort'] ?? 0) - ($a['sort'] ?? 0);
        });

        // 格式化数据，方便前端使用
        $data = [];
        foreach ($list as $item) {
            $data[] = [
                'id' => $item['id'],
                'key' => $item['key'],
                'name' => $item['name'],
                'value' => $item['value'],
                'unit' => $item['unit'],
                'description' => $item['description'] ?? '',
                'icon' => $item['icon'] ?? '',
                'sort' => $item['sort'] ?? 0
            ];
        }

        return app('json')->success($data);
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

        $allData = $this->getAllData();

        foreach ($allData as $item) {
            if (($item['key'] ?? '') == $key && ($item['status'] ?? 0) == 1) {
                return app('json')->success([
                    'id' => $item['id'],
                    'key' => $item['key'],
                    'name' => $item['name'],
                    'value' => $item['value'],
                    'unit' => $item['unit'],
                    'description' => $item['description'] ?? '',
                    'icon' => $item['icon'] ?? ''
                ]);
            }
        }

        return app('json')->fail('数据不存在');
    }
}
