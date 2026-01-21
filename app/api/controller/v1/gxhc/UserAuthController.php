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

namespace app\api\controller\v1\gxhc;

use app\services\gxhc\UserAuthServices;
use crmeb\services\CacheService;
use app\Request;

class UserAuthController
{
    protected $services = NUll;

    /**
     * UserAuthController constructor.
     * @param UserAuthServices $services
     */
    public function __construct(UserAuthServices $services)
    {
        $this->services = $services;
    }

    public function auth(Request $request)
    {
        $postData = $request->postMore([
            ["identity_type", ""],
            ["institution", ""],
            ["institution_type", ""],
            ["business_card", ""],
            ["company_name", ""],
            ["business_position", ""],
            ["industry", []],
            ["business_card2", ""],
        ]);
        $postData['uid'] = (int)$request->uid();
        $this->services->auth($postData);
        return app('json')->success('操作成功');
    }
}
