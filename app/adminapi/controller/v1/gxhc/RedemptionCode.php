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
use app\services\gxhc\RedemptionCodeServices;
use think\facade\App;

/**
 * 兑换码管理控制器（管理后台）
 * Class RedemptionCode
 * @package app\adminapi\controller\v1\gxhc
 */
class RedemptionCode extends AuthController
{
    /**
     * @var RedemptionCodeServices
     */
    protected $services;

    /**
     * RedemptionCode constructor.
     * @param App $app
     * @param RedemptionCodeServices $services
     */
    public function __construct(App $app, RedemptionCodeServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 获取兑换码列表
     * @return mixed
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['page', ''],
            ['limit', ''],
            ['status', ''],
            ['source_type', ''],
            ['channel_code', ''],
            ['agent_id', ''],
            ['code_like', ''],
            ['time', '']
        ]);

        // 处理时间筛选
        // if (!empty($where['time'])) {
        //     $timeArr = explode(' - ', $where['time']);
        //     if (count($timeArr) == 2) {
        //         $where['create_time'] = [strtotime($timeArr[0]), strtotime($timeArr[1]) + 86399];
        //     }
        //     unset($where['time']);
        // }

        // 处理模糊搜索
        if (empty($where['code_like'])) {
            unset($where['code_like']);
        }

        $data = $this->services->getAdminList($where);
        return app('json')->success($data);
    }

    /**
     * 系统批量生成兑换码
     * @return mixed
     */
    public function generate()
    {
        $data = $this->request->postMore([
            ['quantity', 1],
            ['channel_code', ''],
            ['agent_id', ''],
            ['energy_value', 299],
            ['expire_days', 0],
            ['remark', '']
        ]);

        try {
            $result = $this->services->systemGenerateCodes($data);
            return app('json')->success('生成成功', $result);
        } catch (\Exception $e) {
            return app('json')->fail($e->getMessage());
        }
    }

    /**
     * 获取兑换码详情
     * @param int $id
     * @return mixed
     */
    public function read($id)
    {
        if (!$id) {
            return app('json')->fail('参数错误');
        }

        $detail = $this->services->get($id);
        if (!$detail) {
            return app('json')->fail('兑换码不存在');
        }

        return app('json')->success($detail->toArray());
    }

    /**
     * 修改兑换码状态（启用/禁用）
     * @param int $id
     * @param int $status
     * @return mixed
     */
    public function setStatus($id, $status)
    {
        if (!$id) {
            return app('json')->fail('参数错误');
        }

        try {
            $this->services->setCodeStatus((int)$id, (int)$status);
            return app('json')->success('操作成功');
        } catch (\Exception $e) {
            return app('json')->fail($e->getMessage());
        }
    }

    /**
     * 删除兑换码
     * @param int $id
     * @return mixed
     */
    public function delete($id)
    {
        if (!$id) {
            return app('json')->fail('参数错误');
        }

        try {
            $this->services->deleteCode((int)$id);
            return app('json')->success('删除成功');
        } catch (\Exception $e) {
            return app('json')->fail($e->getMessage());
        }
    }

    /**
     * 获取统计概览
     * @return mixed
     */
    public function overview()
    {
        $data = $this->services->getOverviewStatistics();
        return app('json')->success($data);
    }

    /**
     * 获取渠道统计数据
     * @return mixed
     */
    public function channelStatistics()
    {
        $where = $this->request->getMore([
            ['channel_code', ''],
            ['time', '']
        ]);

        // 处理时间筛选
        if (!empty($where['time'])) {
            $timeArr = explode(' - ', $where['time']);
            if (count($timeArr) == 2) {
                $where['create_time'] = [strtotime($timeArr[0]), strtotime($timeArr[1]) + 86399];
            }
            unset($where['time']);
        }

        if (empty($where['channel_code'])) {
            unset($where['channel_code']);
        }

        $data = $this->services->getChannelStatistics($where);
        return app('json')->success($data);
    }

    /**
     * 获取渠道代理报表
     * @return mixed
     */
    public function channelAgentReport()
    {
        $where = $this->request->getMore([
            ['channel_code', ''],
            ['time', '']
        ]);

        // 处理时间筛选
        if (!empty($where['time'])) {
            $timeArr = explode(' - ', $where['time']);
            if (count($timeArr) == 2) {
                $where['create_time'] = [strtotime($timeArr[0]), strtotime($timeArr[1]) + 86399];
            }
            unset($where['time']);
        }

        $data = $this->services->getChannelAgentReport($where);
        return app('json')->success($data);
    }

    /**
     * 获取渠道列表（用于筛选下拉）
     * @return mixed
     */
    public function channelList()
    {
        $channels = [
            ['code' => 'ZB', 'name' => '直播渠道'],
            ['code' => '2B', 'name' => '园区直销'],
            ['code' => 'DL', 'name' => '代理商'],
            ['code' => 'YX', 'name' => '营销活动'],
            ['code' => 'KH', 'name' => '客户推荐']
        ];
        return app('json')->success($channels);
    }

    /**
     * 导出兑换码
     * @return mixed
     */
    public function export()
    {
        $where = $this->request->getMore([
            ['status', ''],
            ['source_type', ''],
            ['channel_code', ''],
            ['agent_id', ''],
            ['time', '']
        ]);

        // 处理时间筛选
        if (!empty($where['time'])) {
            $timeArr = explode(' - ', $where['time']);
            if (count($timeArr) == 2) {
                $where['create_time'] = [strtotime($timeArr[0]), strtotime($timeArr[1]) + 86399];
            }
            unset($where['time']);
        }

        $list = $this->services->getList($where, '*', 0, 0);

        // 格式化导出数据
        $exportData = [];
        foreach ($list as $item) {
            $exportData[] = [
                '兑换码' => $item['code'],
                '能量值' => $item['energy_value'],
                '状态' => $this->getStatusText($item['status']),
                '来源' => $item['source_type'] == 'system' ? '系统生成' : '用户铸造',
                '渠道代码' => $item['channel_code'] ?: '-',
                '代理ID' => $item['agent_id'] ?: '-',
                '创建时间' => $item['create_time'],
                '使用时间' => $item['used_time'] ?: '-',
                '备注' => $item['remark'] ?: '-'
            ];
        }

        return app('json')->success([
            'count' => count($exportData),
            'data' => $exportData
        ]);
    }

    /**
     * 获取状态文本
     * @param int $status
     * @return string
     */
    protected function getStatusText(int $status): string
    {
        $statusMap = [
            0 => '未使用',
            1 => '已使用',
            2 => '已过期',
            3 => '已禁用'
        ];
        return $statusMap[$status] ?? '未知';
    }

    /**
     * 批量生成兑换码表单配置
     * @return mixed
     */
    public function generateForm()
    {
        $form = [
            ['field' => 'quantity', 'title' => '生成数量', 'type' => 'number', 'value' => 1, 'min' => 1, 'max' => 1000],
            ['field' => 'energy_value', 'title' => '能量值', 'type' => 'number', 'value' => 299, 'min' => 1],
            ['field' => 'channel_code', 'title' => '渠道代码', 'type' => 'select', 'value' => '', 'options' => [
                ['label' => '请选择渠道', 'value' => ''],
                ['label' => '直播渠道(ZB)', 'value' => 'ZB'],
                ['label' => '园区直销(2B)', 'value' => '2B'],
                ['label' => '代理商(DL)', 'value' => 'DL'],
                ['label' => '营销活动(YX)', 'value' => 'YX'],
                ['label' => '客户推荐(KH)', 'value' => 'KH']
            ]],
            ['field' => 'agent_id', 'title' => '代理ID', 'type' => 'input', 'value' => '', 'placeholder' => '如：001, 025'],
            ['field' => 'expire_days', 'title' => '有效期(天)', 'type' => 'number', 'value' => 0, 'min' => 0, 'info' => '0表示永不过期'],
            ['field' => 'remark', 'title' => '备注', 'type' => 'textarea', 'value' => '']
        ];

        return app('json')->success($form);
    }

    /**
     * 新增单个兑换码
     * @return mixed
     */
    public function created()
    {
        $data = $this->request->postMore([
            ['energy_value', 0],
            ['channel_code', ''],
            ['agent_id', ''],
            ['expire_time', ''],
            ['remark', '']
        ]);

        try {
            $result = $this->services->adminCreateCode($data);
            return app('json')->success($result['message'], ['id' => $result['id'], 'code' => $result['code']]);
        } catch (\Exception $e) {
            return app('json')->fail($e->getMessage());
        }
    }
}
