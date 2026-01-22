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

namespace app\services\gxhc;

use app\services\BaseServices;
use app\dao\gxhc\FaqDao;
use crmeb\exceptions\AdminException;

/**
 * 常见问题业务服务层
 * Class FaqServices
 * @package app\services\gxhc
 */
class FaqServices extends BaseServices
{
    /**
     * FaqServices constructor.
     * @param FaqDao $dao
     */
    public function __construct(FaqDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取列表
     * @param array $where
     * @return array
     */
    public function getList(array $where): array
    {
        $page = (int)($where['page'] ?? 1);
        $limit = (int)($where['limit'] ?? 10);
        $status = $where['status'] ?? '';
        $category = $where['category'] ?? '';
        $keyword = $where['keyword'] ?? '';

        $query = [];
        if ($status !== '') {
            $query['status'] = $status;
        }
        if ($category !== '') {
            $query['category'] = $category;
        }
        if ($keyword !== '') {
            $query['question'] = $keyword;
        }

        [$list, $count] = $this->dao->getList($query, '*', $page, $limit);

        return compact('list', 'count');
    }

    /**
     * 获取所有启用的常见问题
     * @param string $category
     * @param int $limit
     * @return array
     */
    public function getAllFaq(string $category = '', int $limit = 0): array
    {
        return $this->dao->getAllEnabled($category, $limit);
    }

    /**
     * 获取所有分类
     * @return array
     */
    public function getAllCategories(): array
    {
        return $this->dao->getAllCategories();
    }

    /**
     * 创建常见问题
     * @param array $data
     * @return bool
     */
    public function create(array $data): bool
    {
        $data['add_time'] = time();
        $data['update_time'] = time();

        return (bool)$this->dao->save($data);
    }

    /**
     * 更新常见问题
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $info = $this->dao->get($id);
        if (!$info) {
            throw new AdminException('常见问题不存在');
        }

        $data['update_time'] = time();

        return $this->dao->update($id, $data);
    }

    /**
     * 删除常见问题
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $info = $this->dao->get($id);
        if (!$info) {
            throw new AdminException('常见问题不存在');
        }

        return $this->dao->delete($id);
    }

    /**
     * 修改状态
     * @param int $id
     * @param int $status
     * @return bool
     */
    public function setStatus(int $id, int $status): bool
    {
        $info = $this->dao->get($id);
        if (!$info) {
            throw new AdminException('常见问题不存在');
        }

        return $this->dao->update($id, ['status' => $status, 'update_time' => time()]);
    }

    /**
     * 获取常见问题详情
     * @param int $id
     * @param bool $incView 是否增加查看次数
     * @return array
     */
    public function getInfo(int $id, bool $incView = false): array
    {
        $info = $this->dao->get($id);
        if (!$info) {
            throw new AdminException('常见问题不存在');
        }

        if ($incView) {
            $this->dao->incViewCount($id);
        }

        return $info->toArray();
    }
}
