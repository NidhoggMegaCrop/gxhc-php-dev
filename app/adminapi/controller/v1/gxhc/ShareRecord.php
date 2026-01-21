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
namespace app\adminapi\controller\v1\gxhc;

use app\adminapi\controller\AuthController;
use app\services\gxhc\ShareRecordServices;
use think\facade\App;

/**
 * 直播申请管理
 * Class ShareRecord
 * @package app\adminapi\controller\v1\gxhc
 */
class ShareRecord extends AuthController
{
    /**
     * 构造方法
     * ShareRecord constructor.
     * @param App $app
     * @param ShareRecordServices $services
     */
    public function __construct(App $app, ShareRecordServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 获取直播申请列表
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function get_list()
    {
        $where = $this->request->getMore([
            ['page', ''],
            ['limit', ''],
            ['status', ''],
            ['keyword', ''],
            ['date', '']
        ]);
        return app('json')->success($this->services->get_list($where));
    }
}