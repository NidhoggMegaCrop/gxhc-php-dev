<?php

namespace app\adminapi\controller\v1\system;

use app\adminapi\controller\AuthController;
use app\services\system\crontab\SystemCrontabServices;
use think\facade\App;

class SystemCrontab extends AuthController
{
    public function __construct(App $app, SystemCrontabServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 获取定时任务列表
     * @return mixed
     */
    public function getTimerList()
    {
        $where = ['is_del' => 0];
        return app('json')->success($this->services->getTimerList($where));
    }

    /**
     * 获取定时任务详情
     * @param $id
     * @return mixed
     */
    public function getTimerInfo($id)
    {
        return app('json')->success($this->services->getTimerInfo($id));
    }

    /**
     * 获取定时任务类型
     * @return mixed
     */
    public function getMarkList()
    {
        return app('json')->success($this->services->getMarkList());
    }

    /**
     * 保存定时任务
     * @return mixed
     */
    public function saveTimer()
    {
        $data = [
            'id' => $this->request->param('id', 0),
            'name' => $this->request->param('name', ''),
            'mark' => $this->request->param('mark', ''), // 确保完整获取mark字段
            'content' => $this->request->param('content', ''),
            'type' => $this->request->param('type', 0),
            'is_open' => $this->request->param('is_open', 0),
            'week' => $this->request->param('week', 0),
            'day' => $this->request->param('day', 0),
            'hour' => $this->request->param('hour', 0),
            'minute' => $this->request->param('minute', 0),
            'second' => $this->request->param('second', 0),
        ];

        $this->services->saveTimer($data);
        return app('json')->success(100000);
    }

    /**
     * 删除定时任务
     * @param $id
     * @return mixed
     */
    public function delTimer($id)
    {
        $this->services->delTimer($id);
        return app('json')->success(100002);
    }

    /**
     * 设置定时任务状态
     * @param $id
     * @param $is_open
     * @return mixed
     */
    public function setTimerStatus($id, $is_open)
    {
        $this->services->setTimerStatus($id, $is_open);
        return app('json')->success(100014);
    }
}
