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

namespace app\services\gxhc;

use app\services\BaseServices;
use app\dao\gxhc\MessageCenterDao;
use app\dao\gxhc\MessageCenterReadDao;
use app\model\gxhc\MessageCenter;
use crmeb\exceptions\ApiException;
use think\facade\Log;

/**
 * 消息中心服务类
 * Class MessageCenterServices
 * @package app\services\gxhc
 */
class MessageCenterServices extends BaseServices
{
    /**
     * @var MessageCenterReadDao
     */
    protected $readDao;

    /**
     * MessageCenterServices constructor.
     * @param MessageCenterDao $dao
     * @param MessageCenterReadDao $readDao
     */
    public function __construct(MessageCenterDao $dao, MessageCenterReadDao $readDao)
    {
        $this->dao = $dao;
        $this->readDao = $readDao;
    }

    /**
     * 获取用户消息列表（含定向+广播消息）
     * @param int $uid
     * @param int $category 分类筛选：0=全部
     * @return array
     */
    public function getUserMessageList(int $uid, int $category = 0): array
    {
        [$page, $limit] = $this->getPageValue();

        // 获取用户已读的广播消息ID
        $readBroadcastIds = $this->readDao->getUserReadMessageIds($uid);

        // 获取消息列表
        $list = $this->dao->getUserMessageList($uid, $category, $readBroadcastIds, $page, $limit);
        $count = $this->dao->getUserMessageCount($uid, $category);

        if (!$list) {
            return ['list' => [], 'count' => 0];
        }

        // 处理列表数据
        foreach ($list as &$item) {
            // 处理已读状态：定向消息看look字段，广播消息查read表
            if ($item['is_broadcast'] == 1) {
                $item['is_read'] = in_array($item['id'], $readBroadcastIds) ? 1 : 0;
            } else {
                $item['is_read'] = $item['look'];
            }

            // 分类名称
            $item['category_name'] = MessageCenter::CATEGORY_MAP[$item['category']] ?? '未知';

            // 图标类型（优先使用数据库中的值，否则根据分类自动匹配）
            if (empty($item['icon_type']) || $item['icon_type'] === 'default') {
                $item['icon_type'] = MessageCenter::ICON_MAP[$item['category']] ?? 'default';
            }

            // 时间格式化
            $item['add_time_format'] = date('Y-m-d H:i:s', $item['add_time']);
            $item['time_ago'] = $this->formatTimeAgo($item['add_time']);

            // 处理extra_data
            if (!empty($item['extra_data'])) {
                $item['extra_data'] = is_string($item['extra_data']) ? json_decode($item['extra_data'], true) : $item['extra_data'];
            } else {
                $item['extra_data'] = null;
            }

            // 内容摘要（列表页只显示前100字符）
            $item['content_summary'] = mb_strlen($item['content']) > 100
                ? mb_substr(strip_tags($item['content']), 0, 100) . '...'
                : strip_tags($item['content']);

            // 移除不必要的原始字段
            unset($item['look']);
        }

        return ['list' => $list, 'count' => $count];
    }

    /**
     * 获取消息详情
     * @param int $uid
     * @param int $id
     * @return array
     */
    public function getMessageDetail(int $uid, int $id): array
    {
        $message = $this->dao->get($id);
        if (!$message || $message['is_del'] == 1 || $message['status'] == 0) {
            throw new ApiException(100026);
        }

        $message = $message->toArray();

        // 权限检查：只能查看自己的定向消息或广播消息
        if ($message['is_broadcast'] != 1 && $message['uid'] != $uid) {
            throw new ApiException(100026);
        }

        // 标记为已读
        if ($message['is_broadcast'] == 1) {
            $this->readDao->markAsRead($id, $uid);
        } else {
            if ($message['look'] == 0) {
                $this->dao->update($id, ['look' => 1]);
            }
        }

        // 处理返回数据
        $message['is_read'] = 1; // 查看详情后一定是已读
        $message['category_name'] = MessageCenter::CATEGORY_MAP[$message['category']] ?? '未知';
        if (empty($message['icon_type']) || $message['icon_type'] === 'default') {
            $message['icon_type'] = MessageCenter::ICON_MAP[$message['category']] ?? 'default';
        }
        $message['add_time_format'] = date('Y-m-d H:i:s', $message['add_time']);
        $message['time_ago'] = $this->formatTimeAgo($message['add_time']);

        if (!empty($message['extra_data'])) {
            $message['extra_data'] = is_string($message['extra_data']) ? json_decode($message['extra_data'], true) : $message['extra_data'];
        } else {
            $message['extra_data'] = null;
        }

        return $message;
    }

    /**
     * 获取未读消息数量（红点Badge）
     * @param int $uid
     * @return array
     */
    public function getUnreadCount(int $uid): array
    {
        // 定向消息未读数
        $directUnread = $this->dao->getUnreadDirectCount($uid);

        // 广播消息未读数
        $allBroadcastIds = $this->dao->getAllBroadcastIds();
        $broadcastUnread = $this->readDao->getUnreadBroadcastCount($uid, $allBroadcastIds);

        $total = $directUnread + $broadcastUnread;

        return [
            'total' => $total,
            'has_unread' => $total > 0,
            'detail' => [
                'direct' => $directUnread,
                'broadcast' => $broadcastUnread,
            ]
        ];
    }

    /**
     * 标记消息已读
     * @param int $uid
     * @param int $id 消息ID（0=标记全部已读）
     * @param int $category 分类（0=全部分类）
     * @return bool
     */
    public function markAsRead(int $uid, int $id = 0, int $category = 0): bool
    {
        if ($id > 0) {
            // 标记单条消息已读
            $message = $this->dao->get($id);
            if (!$message || $message['is_del'] == 1) {
                throw new ApiException(100026);
            }
            if ($message['is_broadcast'] == 1) {
                $this->readDao->markAsRead($id, $uid);
            } else {
                if ($message['uid'] == $uid) {
                    $this->dao->update($id, ['look' => 1]);
                }
            }
        } else {
            // 批量标记已读
            // 1. 标记所有定向消息已读
            if ($category > 0) {
                $this->dao->markDirectAsReadByCategory($uid, $category);
            } else {
                $this->dao->markAllDirectAsRead($uid);
            }

            // 2. 标记所有广播消息已读
            $allBroadcastIds = $this->dao->getAllBroadcastIds();
            if (!empty($allBroadcastIds)) {
                $this->readDao->batchMarkAsRead($allBroadcastIds, $uid);
            }
        }

        return true;
    }

    /**
     * 用户删除消息（软删除/标记已删除）
     * @param int $uid
     * @param int $id
     * @return bool
     */
    public function deleteMessage(int $uid, int $id): bool
    {
        $message = $this->dao->get($id);
        if (!$message || $message['is_del'] == 1) {
            throw new ApiException(100026);
        }

        // 只能删除自己的定向消息，广播消息不能被用户删除
        if ($message['is_broadcast'] == 1) {
            throw new ApiException(100026);
        }
        if ($message['uid'] != $uid) {
            throw new ApiException(100026);
        }

        $this->dao->update($id, ['is_del' => 1]);
        return true;
    }

    // ==================== 系统/业务发送消息方法 ====================

    /**
     * 发送资产变动消息
     * @param int $uid 目标用户
     * @param string $title 标题（如"邀请好友成功 +30 能量"）
     * @param string $content 内容详情
     * @param string $jumpUrl 跳转链接
     * @param int $jumpType 跳转类型
     * @param array $extraData 附加数据
     * @return bool
     */
    public function sendAssetChangeMessage(int $uid, string $title, string $content, string $jumpUrl = '', int $jumpType = 0, array $extraData = []): bool
    {
        return $this->sendDirectMessage($uid, MessageCenter::CATEGORY_ASSET_CHANGE, $title, $content, 'lightning', $jumpUrl, $jumpType, $extraData);
    }

    /**
     * 发送服务进度消息
     * @param int $uid 目标用户
     * @param string $title 标题（如"您的 Plan B 深度诊断报告已生成"）
     * @param string $content 内容详情
     * @param string $jumpUrl 跳转链接
     * @param int $jumpType 跳转类型
     * @param array $extraData 附加数据
     * @return bool
     */
    public function sendServiceProgressMessage(int $uid, string $title, string $content, string $jumpUrl = '', int $jumpType = 0, array $extraData = []): bool
    {
        return $this->sendDirectMessage($uid, MessageCenter::CATEGORY_SERVICE_PROGRESS, $title, $content, 'document', $jumpUrl, $jumpType, $extraData);
    }

    /**
     * 发送系统公告（广播）
     * @param string $title 标题
     * @param string $content 富文本内容
     * @param int $adminId 发送管理员ID
     * @return bool
     */
    public function sendSystemAnnouncement(string $title, string $content, int $adminId = 0): bool
    {
        return $this->sendBroadcastMessage(MessageCenter::CATEGORY_SYSTEM_ANNOUNCEMENT, $title, $content, 'megaphone', '', 0, [], $adminId);
    }

    /**
     * 发送内容/活动推送（广播）
     * @param string $title 标题
     * @param string $content 内容
     * @param string $jumpUrl 外部跳转链接
     * @param int $jumpType 跳转类型
     * @param int $adminId 发送管理员ID
     * @param array $extraData 附加数据
     * @return bool
     */
    public function sendContentPush(string $title, string $content, string $jumpUrl = '', int $jumpType = 0, int $adminId = 0, array $extraData = []): bool
    {
        return $this->sendBroadcastMessage(MessageCenter::CATEGORY_CONTENT_PUSH, $title, $content, 'activity', $jumpUrl, $jumpType, $extraData, $adminId);
    }

    /**
     * 发送定向消息（发给特定用户）
     * @param int $uid
     * @param int $category
     * @param string $title
     * @param string $content
     * @param string $iconType
     * @param string $jumpUrl
     * @param int $jumpType
     * @param array $extraData
     * @return bool
     */
    public function sendDirectMessage(int $uid, int $category, string $title, string $content, string $iconType = 'default', string $jumpUrl = '', int $jumpType = 0, array $extraData = []): bool
    {
        try {
            $data = [
                'uid' => $uid,
                'category' => $category,
                'title' => $title,
                'content' => $content,
                'icon_type' => $iconType,
                'jump_url' => $jumpUrl,
                'jump_type' => $jumpType,
                'extra_data' => !empty($extraData) ? json_encode($extraData, JSON_UNESCAPED_UNICODE) : '',
                'is_broadcast' => 0,
                'look' => 0,
                'status' => 1,
                'is_del' => 0,
                'admin_id' => 0,
                'add_time' => time(),
            ];
            $this->dao->save($data);
            return true;
        } catch (\Exception $e) {
            Log::error('消息中心发送定向消息失败: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 发送广播消息（发给所有用户）
     * @param int $category
     * @param string $title
     * @param string $content
     * @param string $iconType
     * @param string $jumpUrl
     * @param int $jumpType
     * @param array $extraData
     * @param int $adminId
     * @return bool
     */
    public function sendBroadcastMessage(int $category, string $title, string $content, string $iconType = 'default', string $jumpUrl = '', int $jumpType = 0, array $extraData = [], int $adminId = 0): bool
    {
        try {
            $data = [
                'uid' => 0,
                'category' => $category,
                'title' => $title,
                'content' => $content,
                'icon_type' => $iconType,
                'jump_url' => $jumpUrl,
                'jump_type' => $jumpType,
                'extra_data' => !empty($extraData) ? json_encode($extraData, JSON_UNESCAPED_UNICODE) : '',
                'is_broadcast' => 1,
                'look' => 0,
                'status' => 1,
                'is_del' => 0,
                'admin_id' => $adminId,
                'add_time' => time(),
            ];
            $this->dao->save($data);
            return true;
        } catch (\Exception $e) {
            Log::error('消息中心发送广播消息失败: ' . $e->getMessage());
            return false;
        }
    }

    // ==================== 管理后台方法 ====================

    /**
     * 管理后台获取消息列表
     * @param array $where
     * @return array
     */
    public function getAdminMessageList(array $where): array
    {
        [$page, $limit] = $this->getPageValue();

        $searchWhere = [];
        $searchWhere['is_del'] = 0;

        if (isset($where['category']) && $where['category'] !== '') {
            $searchWhere['category'] = $where['category'];
        }
        if (isset($where['is_broadcast']) && $where['is_broadcast'] !== '') {
            $searchWhere['is_broadcast'] = $where['is_broadcast'];
        }
        if (isset($where['status']) && $where['status'] !== '') {
            $searchWhere['status'] = $where['status'];
        }

        $list = $this->dao->getAdminMessageList($searchWhere, $page, $limit);
        $count = $this->dao->count($searchWhere);

        // 处理列表数据
        foreach ($list as &$item) {
            $item['category_name'] = MessageCenter::CATEGORY_MAP[$item['category']] ?? '未知';
            if (empty($item['icon_type']) || $item['icon_type'] === 'default') {
                $item['icon_type'] = MessageCenter::ICON_MAP[$item['category']] ?? 'default';
            }
            $item['add_time_format'] = date('Y-m-d H:i:s', $item['add_time']);
            $item['broadcast_text'] = $item['is_broadcast'] ? '全部用户' : ('用户ID: ' . $item['uid']);
            if (!empty($item['extra_data'])) {
                $item['extra_data'] = is_string($item['extra_data']) ? json_decode($item['extra_data'], true) : $item['extra_data'];
            }
        }

        // 关键词搜索（标题/内容）
        if (!empty($where['keyword'])) {
            $keyword = $where['keyword'];
            $list = array_filter($list, function ($item) use ($keyword) {
                return strpos($item['title'] ?? '', $keyword) !== false
                    || strpos($item['content'] ?? '', $keyword) !== false;
            });
            $list = array_values($list);
        }

        return ['list' => $list, 'count' => $count];
    }

    /**
     * 管理后台获取消息详情
     * @param int $id
     * @return array
     */
    public function getAdminMessageDetail(int $id): array
    {
        $message = $this->dao->get($id);
        if (!$message || $message['is_del'] == 1) {
            throw new ApiException(100026);
        }

        $message = $message->toArray();
        $message['category_name'] = MessageCenter::CATEGORY_MAP[$message['category']] ?? '未知';
        $message['add_time_format'] = date('Y-m-d H:i:s', $message['add_time']);

        if (!empty($message['extra_data'])) {
            $message['extra_data'] = is_string($message['extra_data']) ? json_decode($message['extra_data'], true) : $message['extra_data'];
        }

        return $message;
    }

    /**
     * 管理后台创建消息
     * @param array $data
     * @param int $adminId
     * @return bool
     */
    public function adminCreateMessage(array $data, int $adminId): bool
    {
        $category = (int)($data['category'] ?? 0);
        if (!in_array($category, [1, 2, 3, 4])) {
            throw new ApiException(100100);
        }

        if (empty($data['title'])) {
            throw new ApiException(100100);
        }

        $isBroadcast = (int)($data['is_broadcast'] ?? 0);
        $uid = (int)($data['uid'] ?? 0);

        // 广播消息uid设为0
        if ($isBroadcast) {
            $uid = 0;
        }

        // 非广播消息必须有uid
        if (!$isBroadcast && $uid <= 0) {
            throw new ApiException(100100);
        }

        $jumpType = (int)($data['jump_type'] ?? 0);
        $jumpUrl = $data['jump_url'] ?? '';
        $extraData = $data['extra_data'] ?? '';
        $iconType = $data['icon_type'] ?? '';

        // 自动匹配图标
        if (empty($iconType) || $iconType === 'default') {
            $iconType = MessageCenter::ICON_MAP[$category] ?? 'default';
        }

        $saveData = [
            'uid' => $uid,
            'category' => $category,
            'title' => $data['title'],
            'content' => $data['content'] ?? '',
            'icon_type' => $iconType,
            'jump_url' => $jumpUrl,
            'jump_type' => $jumpType,
            'extra_data' => is_array($extraData) ? json_encode($extraData, JSON_UNESCAPED_UNICODE) : $extraData,
            'is_broadcast' => $isBroadcast,
            'look' => 0,
            'status' => (int)($data['status'] ?? 1),
            'is_del' => 0,
            'admin_id' => $adminId,
            'add_time' => time(),
        ];

        $this->dao->save($saveData);
        return true;
    }

    /**
     * 管理后台更新消息
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function adminUpdateMessage(int $id, array $data): bool
    {
        $message = $this->dao->get($id);
        if (!$message || $message['is_del'] == 1) {
            throw new ApiException(100026);
        }

        $updateData = [];
        if (isset($data['title']) && $data['title'] !== '') {
            $updateData['title'] = $data['title'];
        }
        if (isset($data['content'])) {
            $updateData['content'] = $data['content'];
        }
        if (isset($data['icon_type']) && $data['icon_type'] !== '') {
            $updateData['icon_type'] = $data['icon_type'];
        }
        if (isset($data['jump_url'])) {
            $updateData['jump_url'] = $data['jump_url'];
        }
        if (isset($data['jump_type']) && $data['jump_type'] !== '') {
            $updateData['jump_type'] = (int)$data['jump_type'];
        }
        if (isset($data['extra_data'])) {
            $updateData['extra_data'] = is_array($data['extra_data']) ? json_encode($data['extra_data'], JSON_UNESCAPED_UNICODE) : $data['extra_data'];
        }
        if (isset($data['status']) && $data['status'] !== '') {
            $updateData['status'] = (int)$data['status'];
        }

        if (!empty($updateData)) {
            $this->dao->update($id, $updateData);
        }

        return true;
    }

    /**
     * 管理后台删除消息（软删除）
     * @param int $id
     * @return bool
     */
    public function adminDeleteMessage(int $id): bool
    {
        $message = $this->dao->get($id);
        if (!$message) {
            throw new ApiException(100026);
        }
        $this->dao->update($id, ['is_del' => 1]);
        return true;
    }

    /**
     * 管理后台修改消息状态
     * @param int $id
     * @param int $status
     * @return bool
     */
    public function adminSetStatus(int $id, int $status): bool
    {
        $message = $this->dao->get($id);
        if (!$message || $message['is_del'] == 1) {
            throw new ApiException(100026);
        }
        $this->dao->update($id, ['status' => $status]);
        return true;
    }

    /**
     * 管理后台统计概览
     * @return array
     */
    public function getAdminOverview(): array
    {
        $where = ['is_del' => 0];
        $totalCount = $this->dao->getCount($where);

        $broadcastCount = $this->dao->getCount(array_merge($where, ['is_broadcast' => 1]));
        $directCount = $this->dao->getCount(array_merge($where, ['is_broadcast' => 0]));

        $categoryStats = [];
        foreach (MessageCenter::CATEGORY_MAP as $key => $name) {
            $categoryStats[] = [
                'category' => $key,
                'name' => $name,
                'count' => $this->dao->getCount(array_merge($where, ['category' => $key])),
            ];
        }

        return [
            'total' => $totalCount,
            'broadcast' => $broadcastCount,
            'direct' => $directCount,
            'category_stats' => $categoryStats,
        ];
    }

    // ==================== 工具方法 ====================

    /**
     * 格式化相对时间
     * @param int $timestamp
     * @return string
     */
    protected function formatTimeAgo(int $timestamp): string
    {
        $diff = time() - $timestamp;

        if ($diff < 60) {
            return '刚刚';
        } elseif ($diff < 3600) {
            return floor($diff / 60) . '分钟前';
        } elseif ($diff < 86400) {
            return floor($diff / 3600) . '小时前';
        } elseif ($diff < 2592000) {
            return floor($diff / 86400) . '天前';
        } elseif ($diff < 31536000) {
            return floor($diff / 2592000) . '个月前';
        } else {
            return floor($diff / 31536000) . '年前';
        }
    }
}
