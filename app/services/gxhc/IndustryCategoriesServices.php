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
use app\dao\gxhc\IndustryCategoriesDao;
use crmeb\exceptions\AdminException;
use crmeb\services\FormBuilder as Form;
use think\facade\Route as Url;


/**
 *
 * Class IndustryCategoriesServices
 * @package app\services\IndustryCategories
 */
class IndustryCategoriesServices extends BaseServices
{

    /**
     * IndustryCategoriesServices constructor.
     * @param IndustryCategoriesrDao $dao
     */
    public function __construct(IndustryCategoriesDao $dao)
    {
        $this->dao = $dao;
    }

    public function getList($where)
    {
        $where_data = [];
        if (!empty($where['level'])) $where_data[] = ['level', '=', $where['level']];
        $list = $this->dao->getList($where_data, '*');
        return !empty($list) ? $list->toArray() : [];
    }

    // 查询一级下面的二三级分类 getSubList
    public function getSubList($pid)
    {
        $list = $this->dao->getList([['pid', '=', $pid]], '*');
        foreach ($list as &$item) {
            $item['sub'] = $this->dao->getList([['pid', '=', $item['id']]], '*');
        }
        return !empty($list) ? $list->toArray() : [];
    }

}
