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
use app\model\gxhc\MessageCenterRead;

/**
 * 广播消息已读记录DAO
 * Class MessageCenterReadDao
 * @package app\dao\gxhc
 */
class MessageCenterReadDao extends BaseDao
{
    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return MessageCenterRead::class;
    }

    /**
     * 获取用户已读的广播消息ID列表
     * @param int $uid
     * @return array
     */
    public function getUserReadMessageIds(int $uid): array
    {
        return $this->getModel()
            ->where('uid', $uid)
            ->column('message_id');
    }

    /**
     * 检查用户是否已读某条广播消息
     * @param int $messageId
     * @param int $uid
     * @return bool
     */
    public function isRead(int $messageId, int $uid): bool
    {
        return $this->getModel()
            ->where('message_id', $messageId)
            ->where('uid', $uid)
            ->count() > 0;
    }

    /**
     * 标记广播消息为已读
     * @param int $messageId
     * @param int $uid
     * @return bool
     */
    public function markAsRead(int $messageId, int $uid): bool
    {
        if ($this->isRead($messageId, $uid)) {
            return true;
        }
        $this->save([
            'message_id' => $messageId,
            'uid' => $uid,
            'add_time' => time()
        ]);
        return true;
    }

    /**
     * 批量标记广播消息为已读
     * @param array $messageIds
     * @param int $uid
     * @return bool
     */
    public function batchMarkAsRead(array $messageIds, int $uid): bool
    {
        if (empty($messageIds)) {
            return true;
        }
        // 获取已有的已读记录
        $existIds = $this->getModel()
            ->where('uid', $uid)
            ->whereIn('message_id', $messageIds)
            ->column('message_id');

        $newIds = array_diff($messageIds, $existIds);
        if (empty($newIds)) {
            return true;
        }

        $data = [];
        foreach ($newIds as $messageId) {
            $data[] = [
                'message_id' => $messageId,
                'uid' => $uid,
                'add_time' => time()
            ];
        }
        $this->saveAll($data);
        return true;
    }

    /**
     * 获取用户未读的广播消息数量
     * @param int $uid
     * @param array $allBroadcastIds 所有有效广播消息ID
     * @return int
     */
    public function getUnreadBroadcastCount(int $uid, array $allBroadcastIds): int
    {
        if (empty($allBroadcastIds)) {
            return 0;
        }
        $readIds = $this->getUserReadMessageIds($uid);
        $unreadIds = array_diff($allBroadcastIds, $readIds);
        return count($unreadIds);
    }
}
