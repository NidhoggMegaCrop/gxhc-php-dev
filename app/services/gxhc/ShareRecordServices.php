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
use app\dao\gxhc\ShareRecordDao;
use app\services\user\UserServices;
use app\services\user\UserLabelRelationServices;
use crmeb\exceptions\AdminException;
use crmeb\services\FormBuilder as Form;
use think\facade\Route as Url;
use crmeb\exceptions\ApiException;
use app\common\BpAgent;

/**
 *
 * Class ShareRecordServices
 * @package app\services\ShareRecordServices
 */
class ShareRecordServices extends BaseServices
{

    /**
     * ShareRecord constructor.
     * @param ShareRecordDao $dao
     */
    public function __construct(ShareRecordDao $dao)
    {
        $this->dao = $dao;
    }
    public function saveRecord($data)
    {
        // 插入数据库
        $recordId = $this->dao->save($data);

        return $recordId ? true : false;
    }

    public function getInviteColumnByUid($uid, $isNew = 0)
    {
        // 查询指定用户有效邀请的人数
        $count = $this->dao->getColumn([
            'invite_uid' => $uid,
            'is_new' => $isNew,
        ], 'uid');
        return $count;
    }

    public function getInviteCountByUid($uid, $isNew = 0, $status = 1)
    {
        // 查询指定用户有效邀请的人数
        $count = $this->dao->count([
            'invite_uid' => $uid,
            'is_new' => $isNew,
            'status' => $status // 假设有状态字段，1表示有效邀请
        ]);
        return $count;
    }

    public function updateRecordState($uid, $isNew = 0)
    {
        // 将指定用户的状态为1的记录更新为状态2
        $result = $this->dao->update(['invite_uid' => $uid, 'status' => 1, 'is_new' => $isNew], ['status' => 2, 'last_time' => time()]);

        return $result;
    }

    public function get_list($where)
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
        $userService = app()->make(UserServices::class);
        $list = $this->dao->getRecordList($query, $page, $limit);
        foreach ($list as &$item) {
            $userInfo = $userService->getUserInfo($item['uid'], 'nickname,phone,spread_uid');
            $item['nickname'] = $userInfo['nickname'];
            $item['phone'] = $userInfo['phone'];
            $item['spread_uid'] = $userInfo['spread_uid'];
            $item['spread_tag'] = '';
            if ($userInfo['spread_uid']) {
                $spreadInfo = $userService->getOne(['uid' => $userInfo['spread_uid']], 'nickname');
                $item['spread_nickname'] = !empty($spreadInfo['nickname']) ? $spreadInfo['nickname'] : '';
            }

            $userInfo2 = $userService->getUserInfo($item['invite_uid'], 'nickname,phone');
            $item['invite_nickname'] = $userInfo2['nickname'];
            $item['invite_phone'] = $userInfo2['phone'];
        }
        $count = $this->dao->count($query);
        return compact('list', 'count');
    }
}
