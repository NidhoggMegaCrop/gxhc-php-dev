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
use app\dao\gxhc\BattleStatsDao;
use crmeb\exceptions\AdminException;

/**
 * 实战战报统计业务服务层
 * Class BattleStatsServices
 * @package app\services\gxhc
 */
class BattleStatsServices extends BaseServices
{
    /**
     * BattleStatsServices constructor.
     * @param BattleStatsDao $dao
     */
    public function __construct(BattleStatsDao $dao)
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
        $keyword = $where['keyword'] ?? '';

        $query = [];
        if ($status !== '') {
            $query['status'] = $status;
        }
        if ($keyword !== '') {
            $query['name'] = $keyword;
        }

        [$list, $count] = $this->dao->getList($query, '*', $page, $limit);

        return compact('list', 'count');
    }

    /**
     * 获取所有统计项（用于前端展示）
     * @return array
     */
    public function getAllStats(): array
    {
        return $this->dao->getAllEnabled();
    }

    /**
     * 根据key获取统计项
     * @param string $key
     * @return array
     */
    public function getByKey(string $key): array
    {
        $result = $this->dao->getByKey($key);
        if (!$result) {
            throw new AdminException('统计项不存在');
        }
        return $result;
    }

    /**
     * 创建统计项
     * @param array $data
     * @return bool
     */
    public function create(array $data): bool
    {
        // 检查key是否已存在
        if ($this->dao->count(['key' => $data['key']])) {
            throw new AdminException('统计项标识key已存在');
        }

        $data['add_time'] = time();
        $data['update_time'] = time();

        return (bool)$this->dao->save($data);
    }

    /**
     * 更新统计项
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $info = $this->dao->get($id);
        if (!$info) {
            throw new AdminException('统计项不存在');
        }

        // 如果修改了key，检查新key是否已存在
        if (isset($data['key']) && $data['key'] !== $info['key']) {
            if ($this->dao->count(['key' => $data['key']])) {
                throw new AdminException('统计项标识key已存在');
            }
        }

        $data['update_time'] = time();

        return $this->dao->update($id, $data);
    }

    /**
     * 删除统计项
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $info = $this->dao->get($id);
        if (!$info) {
            throw new AdminException('统计项不存在');
        }

        return $this->dao->delete($id);
    }

    /**
     * 更新统计值
     * @param int $id
     * @param int $value
     * @return bool
     */
    public function updateValue(int $id, int $value): bool
    {
        $info = $this->dao->get($id);
        if (!$info) {
            throw new AdminException('统计项不存在');
        }

        return $this->dao->updateValue($id, $value);
    }

    /**
     * 根据key更新统计值
     * @param string $key
     * @param int $value
     * @return bool
     */
    public function updateValueByKey(string $key, int $value): bool
    {
        $info = $this->dao->getByKey($key);
        if (!$info) {
            throw new AdminException('统计项不存在');
        }

        return $this->dao->updateValueByKey($key, $value);
    }

    /**
     * 增加统计值
     * @param string $key
     * @param int $increment
     * @return bool
     */
    public function increment(string $key, int $increment = 1): bool
    {
        return $this->dao->incrementByKey($key, $increment);
    }

    /**
     * 减少统计值
     * @param string $key
     * @param int $decrement
     * @return bool
     */
    public function decrement(string $key, int $decrement = 1): bool
    {
        return $this->dao->decrementByKey($key, $decrement);
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
            throw new AdminException('统计项不存在');
        }

        return $this->dao->update($id, ['status' => $status, 'update_time' => time()]);
    }

    /**
     * 获取统计项详情
     * @param int $id
     * @return array
     */
    public function getInfo(int $id): array
    {
        $info = $this->dao->get($id);
        if (!$info) {
            throw new AdminException('统计项不存在');
        }

        return $info->toArray();
    }
}
