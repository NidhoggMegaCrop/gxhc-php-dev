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
 * 广播消息已读记录模型
 * Class MessageCenterRead
 * @package app\model\gxhc
 */
class MessageCenterRead extends BaseModel
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
    protected $name = 'message_center_read';

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
     * 消息ID搜索器
     * @param Model $query
     * @param $value
     * @param $data
     */
    public function searchMessageIdAttr($query, $value, $data)
    {
        if ($value !== '') {
            if (is_array($value)) {
                $query->whereIn('message_id', $value);
            } else {
                $query->where('message_id', $value);
            }
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
}
