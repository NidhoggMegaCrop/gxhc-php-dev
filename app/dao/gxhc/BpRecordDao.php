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
use app\model\gxhc\BpRecord;

/**
 *
 * Class BpRecordDao
 * @package app\dao\BpRecordDao
 */
class BpRecordDao extends BaseDao
{

    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return BpRecord::class;
    }

    public function getList(array $where, string $field = '*', int $page = 0, int $limit = 0, array $typeWhere = [], $order = 'id desc')
    {
        return $this->search($where)->when(count($typeWhere) > 0, function ($query) use ($typeWhere) {
            $query->where($typeWhere);
        })->field($field)->when($page && $limit, function ($query) use ($page, $limit) {
            $query->page($page, $limit);
        })->order($order)->select()->toArray();
    }

    public function getRecordList(array $where, int $page, int $limit, string $field = '*', string $order = 'id desc'): array
    {
        return $this->search($where)->field($field)->order($order)->page($page, $limit)->select()->toArray();
    }

    public function getQueuedBpRecords($where)
    {
        return $this->getModel()->where($where)->select()->toArray();
    }

    public function getQueuedBpRecords2()
    {
        return $this->getModel()
            ->where(
                '(status <> ? AND status <> ?) AND (progress < ? OR file_id = ?)',
                ['FAILED', 'CANCELED', 100, '']
            )
            ->select()
            ->toArray();
    }
}
