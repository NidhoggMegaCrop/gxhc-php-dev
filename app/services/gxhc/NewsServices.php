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
use app\dao\gxhc\NewsDao;
use crmeb\exceptions\AdminException;

/**
 * 最新动态业务服务层
 * Class NewsServices
 * @package app\services\gxhc
 */
class NewsServices extends BaseServices
{
    /**
     * NewsServices constructor.
     * @param NewsDao $dao
     */
    public function __construct(NewsDao $dao)
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
        $type = $where['type'] ?? '';
        $keyword = $where['keyword'] ?? '';
        $author = $where['author'] ?? '';

        $query = [];
        if ($status !== '') {
            $query['status'] = $status;
        }
        if ($type !== '') {
            $query['type'] = $type;
        }
        if ($keyword !== '') {
            $query['title'] = $keyword;
        }
        if ($author !== '') {
            $query['author'] = $author;
        }

        [$list, $count] = $this->dao->getList($query, '*', $page, $limit);

        // 格式化时间显示
        foreach ($list as &$item) {
            $item['time_ago'] = $this->formatTimeAgo($item['publish_time']);
        }

        return compact('list', 'count');
    }

    /**
     * 获取所有启用的动态
     * @param string $type
     * @param int $limit
     * @return array
     */
    public function getAllNews(string $type = '', int $limit = 0): array
    {
        $list = $this->dao->getAllEnabled($type, $limit);

        // 格式化时间显示
        foreach ($list as &$item) {
            $item['time_ago'] = $this->formatTimeAgo($item['publish_time']);
        }

        return $list;
    }

    /**
     * 创建动态
     * @param array $data
     * @return bool
     */
    public function create(array $data): bool
    {
        $data['add_time'] = time();
        $data['update_time'] = time();

        if (!isset($data['publish_time']) || empty($data['publish_time'])) {
            $data['publish_time'] = time();
        }

        return (bool)$this->dao->save($data);
    }

    /**
     * 更新动态
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $info = $this->dao->get($id);
        if (!$info) {
            throw new AdminException('动态不存在');
        }

        $data['update_time'] = time();

        return $this->dao->update($id, $data);
    }

    /**
     * 删除动态
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $info = $this->dao->get($id);
        if (!$info) {
            throw new AdminException('动态不存在');
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
            throw new AdminException('动态不存在');
        }

        return $this->dao->update($id, ['status' => $status, 'update_time' => time()]);
    }

    /**
     * 获取动态详情
     * @param int $id
     * @param bool $incView 是否增加浏览次数
     * @return array
     */
    public function getInfo(int $id, bool $incView = false): array
    {
        $info = $this->dao->get($id);
        if (!$info) {
            throw new AdminException('动态不存在');
        }

        if ($incView) {
            $this->dao->incViewCount($id);
        }

        $info = $info->toArray();
        $info['time_ago'] = $this->formatTimeAgo($info['publish_time']);

        return $info;
    }

    /**
     * 格式化时间为相对时间
     * @param mixed $time
     * @return string
     */
    private function formatTimeAgo($time): string
    {
        if (!is_numeric($time)) {
            $time = strtotime($time);
        }

        $now = time();
        $diff = $now - $time;

        if ($diff < 60) {
            return '刚才';
        } elseif ($diff < 3600) {
            return floor($diff / 60) . '分钟前';
        } elseif ($diff < 86400) {
            return floor($diff / 3600) . '小时前';
        } elseif ($diff < 2592000) {
            return floor($diff / 86400) . '天前';
        } else {
            return date('Y-m-d', $time);
        }
    }
}
