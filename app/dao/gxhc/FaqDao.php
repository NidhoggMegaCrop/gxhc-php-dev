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
use app\model\gxhc\Faq;

/**
 * 常见问题数据访问层
 * Class FaqDao
 * @package app\dao\gxhc
 */
class FaqDao extends BaseDao
{
    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return Faq::class;
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
     * 获取所有启用的常见问题
     * @param string $category
     * @param int $limit
     * @return array
     */
    public function getAllEnabled(string $category = '', int $limit = 0): array
    {
        return $this->search(['status' => 1])
            ->when($category, function ($query) use ($category) {
                $query->where('category', $category);
            })
            ->order('sort asc,id desc')
            ->when($limit, function ($query) use ($limit) {
                $query->limit($limit);
            })
            ->select()
            ->toArray();
    }

    /**
     * 获取所有分类
     * @return array
     */
    public function getAllCategories(): array
    {
        return $this->getModel()
            ->where('status', 1)
            ->group('category')
            ->column('category');
    }

    /**
     * 增加查看次数
     * @param int $id
     * @return bool
     */
    public function incViewCount(int $id): bool
    {
        return $this->getModel()->where('id', $id)->inc('view_count')->update();
    }
}
