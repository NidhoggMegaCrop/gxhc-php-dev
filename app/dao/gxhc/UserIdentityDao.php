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

namespace app\dao\gxhc;

use app\dao\BaseDao;
use app\model\gxhc\UserIdentity;

/**
 *
 * Class UserIdentityDao
 * @package app\dao\UserIdentityDao
 */
class UserIdentityDao extends BaseDao
{

    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return UserIdentity::class;
    }

    public function getRealName($uid)
    {
        // 查询用户最新的实名认证信息，按添加时间倒序获取第一条
        $result = $this->getModel()->where(['uid' => $uid])->field(['*'])->order(['add_time' => 'desc'])->find();
        
        // 如果数据存在，返回toArray，否则返回null
        return $result ? $result->toArray() : null;
    }
}
