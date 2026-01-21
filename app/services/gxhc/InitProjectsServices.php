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
use app\dao\gxhc\InitProjectsDao;
use crmeb\exceptions\AdminException;
use crmeb\services\FormBuilder as Form;
use think\facade\Route as Url;


/**
 *
 * Class InitProjectsServices
 * @package app\services\initProjects
 */
class InitProjectsServices extends BaseServices
{

    /**
     * InitProjectsServices constructor.
     * @param InitProjectsrDao $dao
     */
    public function __construct(InitProjectsDao $dao)
    {
        $this->dao = $dao;
    }

}
