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
use app\model\gxhc\News;

/**
 * 最新动态数据访问层
 * Class NewsDao
 * @package app\dao\gxhc
 */
class NewsDao extends BaseDao
{
    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return News::class;
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
    public function getList(array $where, string $field = '*', int $page = 0, int $limit = 0, string $order = 'sort asc,publish_time desc,id desc'): array
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
     * 获取所有启用的动态
     * @param string $type
     * @param int $limit
     * @return array
     */
    public function getAllEnabled(string $type = '', int $limit = 0): array
    {
        return $this->search(['status' => 1])
            ->when($type, function ($query) use ($type) {
                $query->where('type', $type);
            })
            ->order('sort asc,publish_time desc,id desc')
            ->when($limit, function ($query) use ($limit) {
                $query->limit($limit);
            })
            ->select()
            ->toArray();
    }

    /**
     * 增加浏览次数
     * @param int $id
     * @return bool
     */
    public function incViewCount(int $id): bool
    {
        return $this->getModel()->where('id', $id)->inc('view_count')->update();
    }
}
