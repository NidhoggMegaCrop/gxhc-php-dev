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
use app\services\gxhc\BattleStatsServices;
use think\facade\App;

/**
 * 实战战报统计管理
 * Class BattleStats
 * @package app\adminapi\controller\v1\gxhc
 */
class BattleStats extends AuthController
{
    /**
     * 构造方法
     * BattleStats constructor.
     * @param App $app
     * @param BattleStatsServices $services
     */
    public function __construct(App $app, BattleStatsServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 获取战报统计列表
     * @return mixed
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['page', 1],
            ['limit', 10],
            ['status', ''],
            ['keyword', '']
        ]);

        $result = $this->services->getList($where);
        return app('json')->success($result);
    }

    /**
     * 获取所有启用的统计项（用于前端展示）
     * @return mixed
     */
    public function getAllStats()
    {
        $list = $this->services->getAllStats();
        return app('json')->success($list);
    }

    /**
     * 获取战报统计详情
     * @param int $id
     * @return mixed
     */
    public function read($id)
    {
        if (!$id) {
            return app('json')->fail('参数错误');
        }

        try {
            $info = $this->services->getInfo((int)$id);
            return app('json')->success($info);
        } catch (\Exception $e) {
            return app('json')->fail($e->getMessage());
        }
    }

    /**
     * 创建战报统计项
     * @return mixed
     */
    public function save()
    {
        $data = $this->request->postMore([
            ['key', ''],
            ['name', ''],
            ['value', 0],
            ['unit', ''],
            ['description', ''],
            ['sort', 0],
            ['icon', ''],
            ['status', 1]
        ]);

        if (empty($data['key'])) {
            return app('json')->fail('请填写统计项标识');
        }
        if (empty($data['name'])) {
            return app('json')->fail('请填写统计项名称');
        }

        try {
            $this->services->create($data);
            return app('json')->success('创建成功');
        } catch (\Exception $e) {
            return app('json')->fail($e->getMessage());
        }
    }

    /**
     * 更新战报统计项
     * @param int $id
     * @return mixed
     */
    public function update($id)
    {
        if (!$id) {
            return app('json')->fail('参数错误');
        }

        $data = $this->request->postMore([
            ['key', ''],
            ['name', ''],
            ['value', 0],
            ['unit', ''],
            ['description', ''],
            ['sort', 0],
            ['icon', ''],
            ['status', 1]
        ]);

        // 移除空值
        $data = array_filter($data, function($value) {
            return $value !== '';
        });

        try {
            $this->services->update((int)$id, $data);
            return app('json')->success('更新成功');
        } catch (\Exception $e) {
            return app('json')->fail($e->getMessage());
        }
    }

    /**
     * 删除战报统计项
     * @param int $id
     * @return mixed
     */
    public function delete($id)
    {
        if (!$id) {
            return app('json')->fail('参数错误');
        }

        try {
            $this->services->delete((int)$id);
            return app('json')->success('删除成功');
        } catch (\Exception $e) {
            return app('json')->fail($e->getMessage());
        }
    }

    /**
     * 修改统计值
     * @param int $id
     * @return mixed
     */
    public function updateValue($id)
    {
        if (!$id) {
            return app('json')->fail('参数错误');
        }

        $value = $this->request->post('value', 0);

        try {
            $this->services->updateValue((int)$id, (int)$value);
            return app('json')->success('更新成功');
        } catch (\Exception $e) {
            return app('json')->fail($e->getMessage());
        }
    }

    /**
     * 修改状态
     * @param int $id
     * @return mixed
     */
    public function setStatus($id)
    {
        if (!$id) {
            return app('json')->fail('参数错误');
        }

        $status = $this->request->post('status', 1);

        try {
            $this->services->setStatus((int)$id, (int)$status);
            return app('json')->success('修改成功');
        } catch (\Exception $e) {
            return app('json')->fail($e->getMessage());
        }
    }
}
