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

namespace app\dao\gxhc;

use app\model\gxhc\ApplyLive;
use app\dao\BaseDao;

/**
 * 直播申请数据访问对象
 * Class ApplyLiveDao
 * @package app\dao\gxhc
 */
class ApplyLiveDao extends BaseDao
{
    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return ApplyLive::class;
    }

    /**
     * 获取直播申请列表
     * @param array $where
     * @param int $page
     * @param int $limit
     * @param string $field
     * @return array
     */
    public function getList(array $where, int $page, int $limit, string $field = '*'): array
    {
        return $this->search($where)->field($field)->page($page, $limit)->select()->toArray();
    }

    /**
     * 根据ID获取直播申请详情
     * @param int $id
     * @return array|null
     */
    public function getInfoById(int $id): ?array
    {
        return $this->get($id)->toArray() ?? null;
    }

    /**
     * 创建直播申请
     * @param array $data
     * @return mixed
     */
    public function createApply(array $data)
    {
        return $this->create($data);
    }

    /**
     * 更新直播申请
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateApply(int $id, array $data): bool
    {
        return $this->update($id, $data);
    }

    /**
     * 删除直播申请
     * @param int $id
     * @return bool
     */
    public function deleteApply(int $id): bool
    {
        return $this->delete($id);
    }

    /**
     * 获取uid=1的最新一条数据
     * @param array $where
     * @return mixed
     */
    public function getApplyList($uid)
    {
        return $this->getModel()
            ->where('uid', '=', $uid)
            ->field('*')
            ->order('add_time', 'desc')
            ->find();
    }
}
