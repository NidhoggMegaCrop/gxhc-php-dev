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

namespace app\services\gxhc;

use app\dao\gxhc\ApplyLiveDao;
use app\services\BaseServices;
use app\services\user\UserServices;
use crmeb\exceptions\AdminException;
use think\exception\ValidateException;

class ApplyLiveServices extends BaseServices
{
    /**
     * ApplyLiveServices constructor.
     * @param ApplyLiveDao $dao
     */
    public function __construct(ApplyLiveDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 申请直播
     * @param array $data
     * @return mixed
     */
    public function apply(array $data)
    {
        // 数据验证
        if (empty($data['name'])) {
            throw new ValidateException('申请人姓名不能为空');
        }

        if (empty($data['project_name'])) {
            throw new ValidateException('项目名称不能为空');
        }

        if (empty($data['expected_date'])) {
            throw new ValidateException('期望日期不能为空');
        }

        if (empty($data['expected_time'])) {
            throw new ValidateException('期望时间不能为空');
        }

        if (empty($data['contact'])) {
            throw new ValidateException('联系方式不能为空');
        }

        $data['add_time'] = time();
        $data['update_time'] = time();
        $data['status'] = 0; // 0-待审核 1-通过 2-拒绝

        return $this->dao->save($data);
    }

    /**
     * 取消直播申请
     * @param int $id
     * @param int $uid
     * @return bool
     */
    public function cancel(int $id, int $uid)
    {
        $info = $this->dao->get($id);
        if (!$info) {
            throw new AdminException('申请记录不存在');
        }

        if ($info['uid'] != $uid) {
            throw new AdminException('无权限操作该记录');
        }

        if ($info['status'] != 0) {
            throw new AdminException('当前状态不允许取消');
        }

        return $this->dao->update($id, ['status' => 3, 'update_time' => time()]); // 3-已取消
    }

    /**
     * 获取已预订的时间槽
     * @param string $date
     * @return array
     */
    public function getBookedSlots(string $date): array
    {
        // 查询指定日期已被预订的时间槽
        // 假设 status 为 0(待审核) 或 1(已通过) 的都算已预订
        $where = [
            ['expected_date', '=', $date],
            ['status', 'in', [0, 1]]
        ];

        $booked = $this->dao->search($where)->field('expected_time')->select()->toArray();

        $timeSlots = [];
        foreach ($booked as $item) {
            $timeSlots[] = $item['expected_time'];
        }

        return $timeSlots;
    }

    /**
     * 检查用户是否已存在未处理的申请
     * @param int $uid
     * @return bool
     */
    public function checkExistingApplication(int $uid): bool
    {
        // 检查是否存在状态为待审核(0)的申请
        $where = [
            ['uid', '=', $uid],
            ['status', '=', 0] // 0-待审核
        ];

        $count = $this->dao->be($where);
        return $count > 0;
    }

    /**
     * 查询用户最新的直播
     * @param int $uid
     * @return array|null
     */
    public function getUserLatestLive(int $uid)
    {
        $info = $this->dao->getApplyList($uid);

        if ($info) {
            return $info->toArray();
        }

        return null;
    }

    /**
     * 获取管理员端直播申请列表
     * @param array $where
     * @return array
     */
    public function getAdminList(array $where)
    {
        $page = (int)($where['page'] ?? 1);
        $limit = (int)($where['limit'] ?? 10);
        $status = $where['status'] ?? '';
        $keyword = $where['keyword'] ?? '';
        $date = $where['date'] ?? '';

        $query = [];

        if ($status !== '') {
            $query['status'] = $status;
        }

        if ($keyword !== '') {
            $query['name|project_name'] = ['like', '%' . $keyword . '%'];
        }

        if ($date !== '') {
            $query['expected_date'] = $date;
        }

        $list = $this->dao->getList($query, $page, $limit);
        $userService = app()->make(UserServices::class);
        foreach ($list as &$item) {
            $userInfo = $userService->getUserInfo($item['uid'], 'nickname,phone,spread_uid');
            $item['nickname'] = $userInfo['nickname'];
            $item['phone'] = $userInfo['phone'];
        }
        $count = $this->dao->count($query);

        return compact('list', 'count');
    }

    /**
     * 获取管理员端直播申请详情
     * @param int $id
     * @return array
     */
    public function getAdminDetail(int $id)
    {
        $info = $this->dao->get($id);
        if (!$info) {
            throw new AdminException('申请记录不存在');
        }

        return $info->toArray();
    }

    /**
     * 审核通过直播申请
     * @param int $id
     * @return bool
     */
    public function approve(int $id)
    {
        $info = $this->dao->get($id);
        if (!$info) {
            throw new AdminException('申请记录不存在');
        }

        return $this->dao->update($id, ['status' => 1, 'update_time' => time()]);
    }

    /**
     * 拒绝直播申请
     * @param int $id
     * @param string $reason
     * @return bool
     */
    public function reject(int $id, string $reason = '')
    {
        $info = $this->dao->get($id);
        if (!$info) {
            throw new AdminException('申请记录不存在');
        }

        $data = [
            'status' => 2,
            'update_time' => time()
        ];

        if (!empty($reason)) {
            $data['reject_reason'] = $reason;
        }

        return $this->dao->update($id, $data);
    }
}
