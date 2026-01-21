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

namespace app\dao\gxhc;

use app\dao\BaseDao;
use app\model\gxhc\BattleStats;

/**
 * 实战战报统计数据访问层
 * Class BattleStatsDao
 * @package app\dao\gxhc
 */
class BattleStatsDao extends BaseDao
{
    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return BattleStats::class;
    }

    /**
     * 获取列表
     * @param array $where
     * @param string $field
     * @param int $page
     * @param int $limit
     * @param string $order
     * @return array
     */
    public function getList(array $where, string $field = '*', int $page = 0, int $limit = 0, string $order = 'sort asc,id desc'): array
    {
        return $this->search($where)
            ->field($field)
            ->when($page && $limit, function ($query) use ($page, $limit) {
                $query->page($page, $limit);
            })
            ->order($order)
            ->select()
            ->toArray();
    }

    /**
     * 根据key获取统计项
     * @param string $key
     * @return array
     */
    public function getByKey(string $key): array
    {
        return $this->search(['key' => $key])->find() ? $this->search(['key' => $key])->find()->toArray() : [];
    }

    /**
     * 获取所有启用的统计项
     * @return array
     */
    public function getAllEnabled(): array
    {
        return $this->search(['status' => 1])
            ->order('sort asc,id desc')
            ->select()
            ->toArray();
    }

    /**
     * 更新统计值
     * @param int $id
     * @param int $value
     * @return bool
     */
    public function updateValue(int $id, int $value): bool
    {
        return $this->update($id, ['value' => $value, 'update_time' => time()]);
    }

    /**
     * 根据key更新统计值
     * @param string $key
     * @param int $value
     * @return bool
     */
    public function updateValueByKey(string $key, int $value): bool
    {
        return $this->getModel()->where('key', $key)->update(['value' => $value, 'update_time' => time()]);
    }

    /**
     * 增加统计值
     * @param string $key
     * @param int $increment
     * @return bool
     */
    public function incrementByKey(string $key, int $increment = 1): bool
    {
        $item = $this->getByKey($key);
        if (!$item) {
            return false;
        }
        return $this->getModel()->where('key', $key)->inc('value', $increment)->update(['update_time' => time()]);
    }

    /**
     * 减少统计值
     * @param string $key
     * @param int $decrement
     * @return bool
     */
    public function decrementByKey(string $key, int $decrement = 1): bool
    {
        $item = $this->getByKey($key);
        if (!$item) {
            return false;
        }
        return $this->getModel()->where('key', $key)->dec('value', $decrement)->update(['update_time' => time()]);
    }
}
