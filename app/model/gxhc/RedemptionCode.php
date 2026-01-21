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

namespace app\model\gxhc;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;
use app\model\user\User;

/**
 * 兑换码模型
 * Class RedemptionCode
 * @package app\model\gxhc
 */
class RedemptionCode extends BaseModel
{
    use ModelTrait;

    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'g_redemption_code';

    protected $autoWriteTimestamp = 'int';

    protected $createTime = 'create_time';

    protected $updateTime = 'update_time';

    /**
     * 状态常量
     */
    const STATUS_UNUSED = 0;    // 未使用
    const STATUS_USED = 1;      // 已使用
    const STATUS_EXPIRED = 2;   // 已过期
    const STATUS_DISABLED = 3;  // 已禁用

    /**
     * 来源类型常量
     */
    const SOURCE_SYSTEM = 'system';  // 系统生成
    const SOURCE_USER = 'user';      // 用户铸造

    /**
     * 默认能量值
     */
    const DEFAULT_ENERGY_VALUE = 299;

     /**
     * 每天生成最大值
     */
    const MAX_MINT_COUNT_PER_DAY = 3;

    /**
     * 添加时间修改器
     * @return int
     */
    public function setCreateTimeAttr()
    {
        return time();
    }

    /**
     * 添加时间获取器
     * @param $value
     * @return false|string
     */
    public function getCreateTimeAttr($value)
    {
        if (!empty($value)) {
            if (is_string($value)) {
                return $value;
            } elseif (is_int($value)) {
                return date('Y-m-d H:i:s', (int)$value);
            }
        }
        return '';
    }

    /**
     * 更新时间获取器
     * @param $value
     * @return false|string
     */
    public function getUpdateTimeAttr($value)
    {
        if (!empty($value)) {
            return date('Y-m-d H:i:s', (int)$value);
        }
        return '';
    }

    /**
     * 使用时间获取器
     * @param $value
     * @return false|string
     */
    public function getUsedTimeAttr($value)
    {
        if (!empty($value)) {
            return date('Y-m-d H:i:s', (int)$value);
        }
        return '';
    }

    /**
     * 过期时间获取器
     * @param $value
     * @return false|string
     */
    public function getExpireTimeAttr($value)
    {
        if (!empty($value)) {
            return date('Y-m-d H:i:s', (int)$value);
        }
        return '';
    }

    /**
     * 关联创建者用户
     * @return \think\model\relation\HasOne
     */
    public function creator()
    {
        return $this->hasOne(User::class, 'uid', 'creator_uid')->field(['uid', 'nickname', 'phone', 'avatar']);
    }

    /**
     * 关联使用者用户
     * @return \think\model\relation\HasOne
     */
    public function usedUser()
    {
        return $this->hasOne(User::class, 'uid', 'used_by_uid')->field(['uid', 'nickname', 'phone', 'avatar']);
    }

    /**
     * 搜索器 - 兑换码
     * @param $query
     * @param $value
     */
    public function searchCodeAttr($query, $value)
    {
        if ($value !== '') {
            $query->where('code', $value);
        }
    }

    /**
     * 搜索器 - 状态
     * @param $query
     * @param $value
     */
    public function searchStatusAttr($query, $value)
    {
        if ($value !== '') {
            if (is_array($value)) {
                $query->whereIn('status', $value);
            } else {
                $query->where('status', $value);
            }
        }
    }

    /**
     * 搜索器 - 来源类型
     * @param $query
     * @param $value
     */
    public function searchSourceTypeAttr($query, $value)
    {
        if ($value !== '') {
            $query->where('source_type', $value);
        }
    }

    /**
     * 搜索器 - 渠道代码
     * @param $query
     * @param $value
     */
    public function searchChannelCodeAttr($query, $value)
    {
        if ($value !== '') {
            $query->where('channel_code', $value);
        }
    }

    /**
     * 搜索器 - 代理ID
     * @param $query
     * @param $value
     */
    public function searchAgentIdAttr($query, $value)
    {
        if ($value !== '') {
            $query->where('agent_id', $value);
        }
    }

    /**
     * 搜索器 - 创建者UID
     * @param $query
     * @param $value
     */
    public function searchCreatorUidAttr($query, $value)
    {
        if ($value !== '') {
            $query->where('creator_uid', $value);
        }
    }

    /**
     * 搜索器 - 使用者UID
     * @param $query
     * @param $value
     */
    public function searchUsedByUidAttr($query, $value)
    {
        if ($value !== '') {
            $query->where('used_by_uid', $value);
        }
    }

    /**
     * 搜索器 - 时间范围
     * @param $query
     * @param $value
     */
    public function searchCreateTimeAttr($query, $value)
    {
        if (is_array($value) && count($value) == 2) {
            $query->whereTime('create_time', 'between', $value);
        }
    }

    /**
     * 搜索器 - 模糊搜索(兑换码)
     * @param $query
     * @param $value
     */
    public function searchCodeLikeAttr($query, $value)
    {
        if ($value !== '') {
            $query->where('code', 'like', "%{$value}%");
        }
    }
}