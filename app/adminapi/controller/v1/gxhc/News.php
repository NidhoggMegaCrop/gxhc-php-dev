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
use app\services\gxhc\NewsServices;
use think\facade\App;

/**
 * 最新动态管理
 * Class News
 * @package app\adminapi\controller\v1\gxhc
 */
class News extends AuthController
{
    /**
     * 构造方法
     * News constructor.
     * @param App $app
     * @param NewsServices $services
     */
    public function __construct(App $app, NewsServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 获取最新动态列表
     * @return mixed
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['page', 1],
            ['limit', 10],
            ['status', ''],
            ['type', ''],
            ['keyword', ''],
            ['author', '']
        ]);

        $result = $this->services->getList($where);
        return app('json')->success($result);
    }

    /**
     * 获取所有启用的动态
     * @return mixed
     */
    public function getAllNews()
    {
        $type = $this->request->get('type', '');
        $limit = (int)$this->request->get('limit', 0);

        $list = $this->services->getAllNews($type, $limit);
        return app('json')->success($list);
    }

    /**
     * 获取动态详情
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
     * 创建动态
     * @return mixed
     */
    public function save()
    {
        $data = $this->request->postMore([
            ['title', ''],
            ['content', ''],
            ['author', ''],
            ['type', 'news'],
            ['cover_image', ''],
            ['sort', 0],
            ['status', 1],
            ['publish_time', '']
        ]);

        if (empty($data['title'])) {
            return app('json')->fail('请填写标题');
        }
        if (empty($data['content'])) {
            return app('json')->fail('请填写内容');
        }

        try {
            $this->services->create($data);
            return app('json')->success('创建成功');
        } catch (\Exception $e) {
            return app('json')->fail($e->getMessage());
        }
    }

    /**
     * 更新动态
     * @param int $id
     * @return mixed
     */
    public function update($id)
    {
        if (!$id) {
            return app('json')->fail('参数错误');
        }

        $data = $this->request->postMore([
            ['title', ''],
            ['content', ''],
            ['author', ''],
            ['type', ''],
            ['cover_image', ''],
            ['sort', 0],
            ['status', 1],
            ['publish_time', '']
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
     * 删除动态
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
