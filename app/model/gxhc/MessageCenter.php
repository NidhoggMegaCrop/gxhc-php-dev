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
use think\Model;

/**
 * 消息中心模型
 * Class MessageCenter
 * @package app\model\gxhc
 */
class MessageCenter extends BaseModel
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
    protected $name = 'message_center';

    protected $insert = ['add_time'];

    /**
     * 添加时间修改器
     * @return int
     */
    public function setAddTimeAttr()
    {
        return time();
    }

    /**
     * 消息分类常量
     */
    const CATEGORY_ASSET_CHANGE = 1;       // 资产变动
    const CATEGORY_SERVICE_PROGRESS = 2;   // 服务进度
    const CATEGORY_SYSTEM_ANNOUNCEMENT = 3; // 系统公告
    const CATEGORY_CONTENT_PUSH = 4;       // 内容/活动推送

    /**
     * 跳转类型常量
     */
    const JUMP_NONE = 0;      // 无跳转
    const JUMP_INTERNAL = 1;  // 内部页面
    const JUMP_EXTERNAL = 2;  // 外部链接

    /**
     * 图标类型映射
     */
    const ICON_MAP = [
        1 => 'lightning',   // 资产变动 - 闪电图标
        2 => 'document',    // 服务进度 - 文档图标
        3 => 'megaphone',   // 系统公告 - 喇叭图标
        4 => 'activity',    // 内容/活动推送 - 活动图标
    ];

    /**
     * 分类名称映射
     */
    const CATEGORY_MAP = [
        1 => '资产变动',
        2 => '服务进度',
        3 => '系统公告',
        4 => '内容/活动推送',
    ];

    /**
     * ID搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchIdAttr($query, $value, $data)
    {
        if ($value !== '') {
            $query->where('id', $value);
        }
    }

    /**
     * UID搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchUidAttr($query, $value, $data)
    {
        if ($value !== '') {
            $query->where('uid', $value);
        }
    }

    /**
     * 分类搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchCategoryAttr($query, $value, $data)
    {
        if ($value !== '') {
            $query->where('category', $value);
        }
    }

    /**
     * 广播消息搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchIsBroadcastAttr($query, $value, $data)
    {
        if ($value !== '') {
            $query->where('is_broadcast', $value);
        }
    }

    /**
     * 状态搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchStatusAttr($query, $value, $data)
    {
        if ($value !== '') {
            $query->where('status', $value);
        }
    }

    /**
     * 删除搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchIsDelAttr($query, $value, $data)
    {
        $query->where('is_del', $value);
    }

    /**
     * 已读搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchLookAttr($query, $value, $data)
    {
        if ($value !== '') {
            $query->where('look', $value);
        }
    }
}
