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
use app\dao\gxhc\ProjectTeamInfoDao;
use crmeb\exceptions\AdminException;
use crmeb\services\FormBuilder as Form;
use think\facade\Route as Url;


/**
 *
 * Class ProjectTeamInfoServices
 * @package app\services\projectTeamInfo
 */
class ProjectTeamInfoServices extends BaseServices
{

    /**
     * ProjectTeamInfoServices constructor.
     * @param ProjectTeamInforDao $dao
     */
    public function __construct(ProjectTeamInfoDao $dao)
    {
        $this->dao = $dao;
    }

}
