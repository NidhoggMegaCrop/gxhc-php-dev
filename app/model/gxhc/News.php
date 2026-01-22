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

/**
 * 最新动态模型
 * Class News
 * @package app\model\gxhc
 */
class News extends BaseModel
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
    protected $name = 'g_news';

    /**
     * 自动时间戳
     * @var bool
     */
    protected $autoWriteTimestamp = true;

    /**
     * 创建时间字段
     * @var string
     */
    protected $createTime = 'add_time';

    /**
     * 更新时间字段
     * @var string
     */
    protected $updateTime = 'update_time';

    /**
     * 时间字段取出后的默认时间格式
     * @var string
     */
    protected $dateFormat = false;

    /**
     * 添加时间获取器
     * @param $value
     * @return false|string
     */
    public function getAddTimeAttr($value)
    {
        return $value ? date('Y-m-d H:i:s', $value) : '';
    }

    /**
     * 更新时间获取器
     * @param $value
     * @return false|string
     */
    public function getUpdateTimeAttr($value)
    {
        return $value ? date('Y-m-d H:i:s', $value) : '';
    }

    /**
     * 发布时间获取器
     * @param $value
     * @return false|string
     */
    public function getPublishTimeAttr($value)
    {
        return $value ? date('Y-m-d H:i:s', $value) : '';
    }

    /**
     * 发布时间修改器
     * @param $value
     * @return false|int
     */
    public function setPublishTimeAttr($value)
    {
        return is_numeric($value) ? $value : strtotime($value);
    }

    /**
     * 添加时间修改器
     * @param $value
     * @return false|int
     */
    public function setAddTimeAttr($value)
    {
        return is_numeric($value) ? $value : strtotime($value);
    }

    /**
     * 更新时间修改器
     * @param $value
     * @return false|int
     */
    public function setUpdateTimeAttr($value)
    {
        return is_numeric($value) ? $value : strtotime($value);
    }

    /**
     * 状态搜索器
     * @param $query
     * @param $value
     */
    public function searchStatusAttr($query, $value)
    {
        if ($value !== '') {
            $query->where('status', $value);
        }
    }

    /**
     * 类型搜索器
     * @param $query
     * @param $value
     */
    public function searchTypeAttr($query, $value)
    {
        if ($value) {
            $query->where('type', $value);
        }
    }

    /**
     * 标题搜索器
     * @param $query
     * @param $value
     */
    public function searchTitleAttr($query, $value)
    {
        if ($value) {
            $query->whereLike('title', '%' . $value . '%');
        }
    }

    /**
     * 作者搜索器
     * @param $query
     * @param $value
     */
    public function searchAuthorAttr($query, $value)
    {
        if ($value) {
            $query->whereLike('author', '%' . $value . '%');
        }
    }
}
