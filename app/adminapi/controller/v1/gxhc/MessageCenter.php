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
use app\services\gxhc\MessageCenterServices;
use think\facade\App;

/**
 * 消息中心管理
 * Class MessageCenter
 * @package app\adminapi\controller\v1\gxhc
 */
class MessageCenter extends AuthController
{
    /**
     * @var MessageCenterServices
     */
    protected $services;

    /**
     * MessageCenter constructor.
     * @param App $app
     * @param MessageCenterServices $services
     */
    public function __construct(App $app, MessageCenterServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 消息列表
     * @return mixed
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['category', ''],
            ['is_broadcast', ''],
            ['status', ''],
            ['keyword', '']
        ]);
        return app('json')->success($this->services->getAdminMessageList($where));
    }

    /**
     * 消息详情
     * @param int $id
     * @return mixed
     */
    public function read($id)
    {
        if (!$id) {
            return app('json')->fail('参数错误');
        }
        return app('json')->success($this->services->getAdminMessageDetail((int)$id));
    }

    /**
     * 创建消息（系统公告/内容推送）
     * @return mixed
     */
    public function save()
    {
        $data = $this->request->postMore([
            ['category', 0],
            ['title', ''],
            ['content', ''],
            ['icon_type', ''],
            ['jump_url', ''],
            ['jump_type', 0],
            ['extra_data', ''],
            ['is_broadcast', 1],
            ['uid', 0],
            ['status', 1]
        ]);

        if (empty($data['title'])) {
            return app('json')->fail('请填写消息标题');
        }

        $this->services->adminCreateMessage($data, $this->adminId);
        return app('json')->success('创建成功');
    }

    /**
     * 更新消息
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
            ['icon_type', ''],
            ['jump_url', ''],
            ['jump_type', ''],
            ['extra_data', ''],
            ['status', '']
        ]);

        $this->services->adminUpdateMessage((int)$id, $data);
        return app('json')->success('更新成功');
    }

    /**
     * 删除消息
     * @param int $id
     * @return mixed
     */
    public function delete($id)
    {
        if (!$id) {
            return app('json')->fail('参数错误');
        }
        $this->services->adminDeleteMessage((int)$id);
        return app('json')->success('删除成功');
    }

    /**
     * 修改消息状态
     * @param int $id
     * @return mixed
     */
    public function setStatus($id)
    {
        if (!$id) {
            return app('json')->fail('参数错误');
        }
        $status = (int)$this->request->post('status', 1);
        $this->services->adminSetStatus((int)$id, $status);
        return app('json')->success('修改成功');
    }

    /**
     * 统计概览
     * @return mixed
     */
    public function overview()
    {
        return app('json')->success($this->services->getAdminOverview());
    }
}
