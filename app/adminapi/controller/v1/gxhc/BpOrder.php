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
use app\services\gxhc\BpOrderServices;
use think\facade\App;

/**
 * 直播申请管理
 * Class BpOrder
 * @package app\adminapi\controller\v1\gxhc
 */
class BpOrder extends AuthController
{
    /**
     * 构造方法
     * BpOrder constructor.
     * @param App $app
     * @param BpOrderServices $services
     */
    public function __construct(App $app, BpOrderServices $services)
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
        return app('json')->success($this->services->get_record_list($where));
    }

    /**
     * 获取直播申请详情
     * @param $id
     * @return mixed
     */
    public function read($id)
    {
        if (!$id) return app('json')->fail(100100);
        $info = $this->services->getAdminDetail($id);
        return app('json')->success($info);
    }

    /**
     * 删除直播申请
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        if (!$id) return app('json')->fail(100100);
        $res = $this->services->delete($id);
        if (!$res)
            return app('json')->fail(100008);
        else
            return app('json')->success(100002);
    }
}