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
declare (strict_types=1);

namespace app\dao\gxhc;

use app\dao\BaseDao;
use app\model\gxhc\MessageCenter;

/**
 * 消息中心DAO
 * Class MessageCenterDao
 * @package app\dao\gxhc
 */
class MessageCenterDao extends BaseDao
{
    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return MessageCenter::class;
    }

    /**
     * 获取用户的消息列表（包含定向消息和广播消息）
     * @param int $uid 用户ID
     * @param int $category 消息分类（0=全部）
     * @param array $readMessageIds 已读的广播消息ID数组
     * @param int $page 页码
     * @param int $limit 每页条数
     * @return array
     */
    public function getUserMessageList(int $uid, int $category = 0, array $readMessageIds = [], int $page = 0, int $limit = 0)
    {
        return $this->getModel()
            ->where('is_del', 0)
            ->where('status', 1)
            ->where(function ($query) use ($uid) {
                // 定向消息（发给当前用户的）或广播消息
                $query->where('uid', $uid)->whereOr('is_broadcast', 1);
            })
            ->when($category > 0, function ($query) use ($category) {
                $query->where('category', $category);
            })
            ->field('id,uid,category,title,content,icon_type,jump_url,jump_type,extra_data,is_broadcast,look,add_time')
            ->when($page && $limit, function ($query) use ($page, $limit) {
                $query->page($page, $limit);
            })
            ->order('add_time desc')
            ->select()
            ->toArray();
    }

    /**
     * 获取用户消息总数
     * @param int $uid
     * @param int $category
     * @return int
     */
    public function getUserMessageCount(int $uid, int $category = 0): int
    {
        return $this->getModel()
            ->where('is_del', 0)
            ->where('status', 1)
            ->where(function ($query) use ($uid) {
                $query->where('uid', $uid)->whereOr('is_broadcast', 1);
            })
            ->when($category > 0, function ($query) use ($category) {
                $query->where('category', $category);
            })
            ->count();
    }

    /**
     * 获取用户未读的定向消息数量
     * @param int $uid
     * @return int
     */
    public function getUnreadDirectCount(int $uid): int
    {
        return $this->getModel()
            ->where('uid', $uid)
            ->where('is_del', 0)
            ->where('status', 1)
            ->where('is_broadcast', 0)
            ->where('look', 0)
            ->count();
    }

    /**
     * 获取所有有效的广播消息ID
     * @return array
     */
    public function getAllBroadcastIds(): array
    {
        return $this->getModel()
            ->where('is_broadcast', 1)
            ->where('is_del', 0)
            ->where('status', 1)
            ->column('id');
    }

    /**
     * 管理后台消息列表
     * @param array $where
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getAdminMessageList(array $where, int $page = 0, int $limit = 0): array
    {
        return $this->search($where)
            ->when($page && $limit, function ($query) use ($page, $limit) {
                $query->page($page, $limit);
            })
            ->order('add_time desc')
            ->select()
            ->toArray();
    }

    /**
     * 将用户的所有定向未读消息标记为已读
     * @param int $uid
     * @return bool
     */
    public function markAllDirectAsRead(int $uid): bool
    {
        $this->getModel()
            ->where('uid', $uid)
            ->where('is_broadcast', 0)
            ->where('is_del', 0)
            ->where('look', 0)
            ->update(['look' => 1]);
        return true;
    }

    /**
     * 按分类将用户的定向未读消息标记为已读
     * @param int $uid
     * @param int $category
     * @return bool
     */
    public function markDirectAsReadByCategory(int $uid, int $category): bool
    {
        $this->getModel()
            ->where('uid', $uid)
            ->where('is_broadcast', 0)
            ->where('is_del', 0)
            ->where('look', 0)
            ->where('category', $category)
            ->update(['look' => 1]);
        return true;
    }
}
