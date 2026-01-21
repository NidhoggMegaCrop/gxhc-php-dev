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

use app\services\user\UserServices;
use app\services\user\UserLabelRelationServices;
use app\services\BaseServices;
use app\dao\gxhc\FeedBackDao;
use crmeb\exceptions\AdminException;
use crmeb\services\FormBuilder as Form;
use think\facade\Route as Url;


/**
 *
 * Class FeedBackServices
 * @package app\services\FeedBack
 */
class FeedBackServices extends BaseServices
{

    /**
     * FeedBackServices constructor.
     * @param FeedBackrDao $dao
     */
    public function __construct(FeedBackDao $dao)
    {
        $this->dao = $dao;
    }

    public function get_feedback_list(array $where)
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
        $bpOrderService = app()->make(BpOrderServices::class);
        $userLabelRelationServices = app()->make(UserLabelRelationServices::class);
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
                $userTags = $userLabelRelationServices->getUserLabelList(['uid' => $userInfo['spread_uid']]);
                $item['spread_userTag'] = !empty($userTags) ? $userTags : '';
            }
            $item['price'] = !empty($order_info['pay_price']) ? $order_info['pay_price'] : '';
        }
        $count = $this->dao->count($query);
        return compact('list', 'count');
    }
}
