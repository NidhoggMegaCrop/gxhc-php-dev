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
use app\model\gxhc\RedemptionCode;

/**
 * 兑换码数据访问层
 * Class RedemptionCodeDao
 * @package app\dao\gxhc
 */
class RedemptionCodeDao extends BaseDao
{
    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return RedemptionCode::class;
    }

    /**
     * 获取兑换码列表
     * @param array $where
     * @param string $field
     * @param int $page
     * @param int $limit
     * @param string $order
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getList(array $where, string $field = '*', int $page = 0, int $limit = 0, string $order = 'id desc')
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
     * 获取兑换码列表（带关联用户信息）
     * @param array $where
     * @param string $field
     * @param int $page
     * @param int $limit
     * @param string $order
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getListWithUser(array $where, string $field = '*', int $page = 0, int $limit = 0, string $order = 'id desc')
    {
        return $this->search($where)
            ->field($field)
            ->with(['creator', 'usedUser'])
            ->when($page && $limit, function ($query) use ($page, $limit) {
                $query->page($page, $limit);
            })
            ->order($order)
            ->select()
            ->toArray();
    }

    /**
     * 根据兑换码获取记录
     * @param string $code
     * @return array|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getByCode(string $code)
    {
        return $this->getModel()->where('code', $code)->find();
    }

    /**
     * 检查兑换码是否存在
     * @param string $code
     * @return bool
     */
    public function codeExists(string $code): bool
    {
        return $this->getModel()->where('code', $code)->count() > 0;
    }

    /**
     * 批量保存兑换码
     * @param array $data
     * @return bool
     */
    public function saveAll(array $data)
    {
        return $this->getModel()->saveAll($data);
    }

    /**
     * 获取渠道统计数据
     * @param array $where
     * @return array
     */
    public function getChannelStatistics(array $where = [])
    {
        return $this->search($where)
            ->field('channel_code, agent_id, COUNT(*) as total_count,
                    SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as used_count,
                    SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) as unused_count,
                    SUM(CASE WHEN status = 1 THEN energy_value ELSE 0 END) as used_energy')
            ->group('channel_code, agent_id')
            ->select()
            ->toArray();
    }

    /**
     * 获取渠道汇总统计
     * @param array $where
     * @return array
     */
    public function getChannelSummary(array $where = [])
    {
        return $this->search($where)
            ->field('channel_code,
                    COUNT(*) as total_count,
                    SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as used_count,
                    SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) as unused_count,
                    COUNT(DISTINCT agent_id) as agent_count,
                    SUM(energy_value) as total_energy,
                    SUM(CASE WHEN status = 1 THEN energy_value ELSE 0 END) as used_energy')
            ->group('channel_code')
            ->select()
            ->toArray();
    }

    /**
     * 获取用户铸造的兑换码列表
     * @param int $uid
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getUserMintedCodes(int $uid, int $page = 0, int $limit = 0)
    {
        return $this->getModel()
            ->where('creator_uid', $uid)
            ->where('source_type', RedemptionCode::SOURCE_USER)
            ->when($page && $limit, function ($query) use ($page, $limit) {
                $query->page($page, $limit);
            })
            ->order('id desc')
            ->select()
            ->toArray();
    }

    /**
     * 获取用户铸造的兑换码数量
     * @param int $uid
     * @return int
     */
    public function getUserMintedCount(int $uid): int
    {
        return $this->getModel()
            ->where('creator_uid', $uid)
            ->where('source_type', RedemptionCode::SOURCE_USER)
            ->count();
    }

    public function getUserUseCodes(int $uid, int $page = 0, int $limit = 0)
    {
        return $this->getModel()
            ->where('used_by_uid', $uid)
            ->where('source_type', RedemptionCode::SOURCE_USER)
            ->when($page && $limit, function ($query) use ($page, $limit) {
                $query->page($page, $limit);
            })
            ->order('id desc')
            ->select()
            ->toArray();
    }
    
    public function getUserUseCount(int $uid): int
    {
        return $this->getModel()
            ->where('used_by_uid', $uid)
            ->where('source_type', RedemptionCode::SOURCE_USER)
            ->count();
    }

    /**
     * 更新兑换码状态
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateStatus(int $id, array $data)
    {
        return $this->getModel()->where('id', $id)->update($data);
    }

    /**
     * 获取过期的兑换码IDs
     * @return array
     */
    public function getExpiredCodeIds(): array
    {
        return $this->getModel()
            ->where('status', RedemptionCode::STATUS_UNUSED)
            ->where('expire_time', '>', 0)
            ->where('expire_time', '<', time())
            ->column('id');
    }

    /**
     * 批量更新为过期状态
     * @param array $ids
     * @return bool
     */
    public function batchSetExpired(array $ids)
    {
        if (empty($ids)) {
            return true;
        }
        return $this->getModel()
            ->whereIn('id', $ids)
            ->update(['status' => RedemptionCode::STATUS_EXPIRED, 'update_time' => time()]);
    }

    /**
     * 获取用户当天铸造的兑换码数量
     * @param int $uid
     * @return int
     */
    public function getUserTodayMintedCount($uid)
    {
        $todayStart = strtotime(date('Y-m-d'));      // 今天开始的时间戳
        $todayEnd = $todayStart + 86400 - 1;        // 今天结束的时间戳

        return $this->getModel()
            ->where('creator_uid', $uid)
            ->where('create_time', '>=', $todayStart)
            ->where('create_time', '<=', $todayEnd)
            ->where('source_type', RedemptionCode::SOURCE_USER)
            ->count();
    }
}
