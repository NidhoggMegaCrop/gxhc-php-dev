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
use app\dao\gxhc\UserIdentityDao;
use crmeb\exceptions\AdminException;
use crmeb\services\FormBuilder as Form;
use think\facade\Route as Url;


/**
 *
 * Class UserIdentityServices
 * @package app\services\UserIdentity
 */
class UserIdentityServices extends BaseServices
{

    /**
     * UserIdentityServices constructor.
     * @param UserIdentityrDao $dao
     */
    public function __construct(UserIdentityDao $dao)
    {
        $this->dao = $dao;
    }

    public function getRealName($uid)
    {
        // 查询用户最新的实名认证信息，按添加时间倒序获取第一条
        return $this->dao->getRealName($uid);
    }
}
