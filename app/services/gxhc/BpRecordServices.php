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
use app\dao\gxhc\BpRecordDao;
use app\services\user\UserServices;
use app\services\user\UserLabelRelationServices;
use app\services\gxhc\BpOrderServices;
use crmeb\exceptions\AdminException;
use crmeb\services\FormBuilder as Form;
use think\facade\Route as Url;
use crmeb\exceptions\ApiException;
use app\common\BpAgent;
use app\services\message\notice\{
    SmsService
};

/**
 *
 * Class BpRecordServices
 * @package app\services\BpRecord
 */
class BpRecordServices extends BaseServices
{

    /**
     * BpRecordServices constructor.
     * @param BpRecordDao $dao
     */
    public function __construct(BpRecordDao $dao)
    {
        $this->dao = $dao;
    }

    public function get_record_list($where)
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
            $order_info = $bpOrderService->getOne([
                'order_id' => $item['order_id'],
                'status' => 'SUCCEEDED'
            ], 'pay_price,order_id');
            $item['price'] = !empty($order_info['pay_price']) ? $order_info['pay_price'] : '';
        }
        $count = $this->dao->count($query);
        return compact('list', 'count');
    }

    public function bpStatusUpdate()
    {
        echo 1;
        // ini_set('memory_limit', '100M');
        set_time_limit(0);

        $userService = app()->make(UserServices::class);

        $queuedRecords = $this->dao->getQueuedBpRecords2();
        // dumpSql();
        // dump($queuedRecords);
        // exit;

        if (!empty($queuedRecords)) {
            foreach ($queuedRecords as $record) {
                $runId = $record['run_id'];
                $temp = [
                    'status' => '',
                    'progress' => 0,
                    'file_path' => ''
                ];

                // 1.查询详情接口 更新进度
                $result = BpAgent::getRunInfo($runId);
                // dump($result);
                if ($result['code'] == 200) {
                    $temp['status'] = !empty($result['data']['status']) ? $result['data']['status'] : '';
                    $temp['progress'] = !empty($result['data']['progress']) ? $result['data']['progress'] : 0;
                    $temp['file_path'] = !empty($result['data']['input_files']['files'][0]['file_path']) ? $result['data']['input_files']['files'][0]['file_path'] : '';
                    $this->updateBpInfo([
                        'run_id' => $runId,
                        'uid' => $record['uid'],
                        'target' => $record['target'],
                    ], [
                        'status' => $temp['status'],
                        'file_path' => $temp['file_path'],
                        'last_time' => time(),
                        'progress' => $temp['progress']
                    ]);
                }
                // dump($result);
                // 添加1秒延迟，控制请求频率
                sleep(1);
                // dump($result);

                // 检查进度是否完成
                if (isset($result['data']['progress']) && $result['data']['progress'] >= 100) {
                    // 2.查询文件下载地址 file_id
                    $result2 = BpAgent::getTaskInfo($runId, $record['target']);
                    // dump($result2['data'][0]['status']);
                    // dump($result2);
                    if ($result2['code'] == 200) {
                        $upData = [
                            'info_response' => json_encode($result2['data']),
                            'last_time' => time(),
                            'status' => $result2['data'][0]['status'] ?? '',
                        ];
                        if (!empty($result2['data'][0]['status']) && $result2['data'][0]['status'] == 'SUCCEEDED') {
                            $temp['file_id'] = !empty($result2['data'][0]['output_data']['additional_files'][0]['file_id']) ? $result2['data'][0]['output_data']['additional_files'][0]['file_id'] : '';
                            $temp['filename'] = !empty($result2['data'][0]['output_data']['additional_files'][0]['filename']) ? $result2['data'][0]['output_data']['additional_files'][0]['filename'] : '';
                            $temp['file_path'] = !empty($result2['data'][0]['output_data']['additional_files'][0]['file_path']) ? $result2['data'][0]['output_data']['additional_files'][0]['file_path'] : '';
                            $upData['file_id'] = $temp['file_id'];
                            $upData['filename'] = $temp['filename'];
                            $upData['file_path'] = $temp['file_path'];
                        }
                        $this->updateBpInfo([
                            'run_id' => $runId,
                            'uid' => $record['uid'],
                            'target' => $record['target'],
                        ], $upData);
                    }
                    // 发送短信通知
                    $userPhone = $userService->value($record['uid'], 'phone');
                    if ($userPhone) {
                        $NoticeSms = app()->make(SmsService::class);
                        try {
                            // $res = $NoticeSms->sendDx(true, $userPhone, [], $record['target'] == 'export_preliminary' ? 'bp_a' : 'bp_b');
                        } catch (\Exception $e) {
                            $res = false;
                        }
                    }
                }
            }
        }
    }

    /**
     * 保存BP记录
     *
     * @param int $uid 用户ID
     * @param array $data BP相关数据
     * @return bool
     */
    public function saveBpRecord($uid, $data)
    {
        // 直接保存数据到数据库
        $bpRecord = [
            'uid' => $uid,
            'run_id' => $data['run_id'] ?? '',
            'pipeline' => $data['pipeline'] ?? '',
            'target' => $data['target'] ?? '',
            'is_create' => !empty($data['is_create']) ? $data['is_create'] : '1',
            'order_id' => !empty($data['order_id']) ? $data['order_id'] : '',
            'filename' => $data['filename'] ?? '',
            'origina_filename' => !empty($data['origina_filename']) ? $data['origina_filename'] : '',
            'status' => $data['status'] ?? '',
            'add_time' => $data['add_time'] ?? time(),
            'run_response' => $data['run_response'] ?? '',
        ];

        // 插入数据库
        $recordId = $this->dao->save($bpRecord);

        return $recordId ? $recordId->id : false;
    }

    /**
     * 更新BP记录信息
     *
     * @param int $where 
     * @param array $data BP相关信息
     * @return bool
     */
    public function updateBpInfo($where, $data)
    {
        // 更新数据库记录
        $result = $this->dao->update($where, $data);

        return $result !== false;
    }

    public function getBpInfo($uid, $prunId, $targets)
    {
        $res = $this->dao->getOne(['uid' => $uid, 'run_id' => $prunId, 'target' => $targets]);
        return $res ? $res->toArray() : [];
    }

    public function getBpResultList(int $uid = 0, $where_time = [], string $field = '*')
    {
        [$page, $limit] = $this->getPageValue();
        $where = ['uid' => $uid];
        if ($where_time) $where['add_time'] = $where_time;
        $list = $this->dao->getList($where, $field, $page, $limit);
        $count = $this->dao->count($where);
        return compact('list', 'count');
    }

    public function getBpResultInfo(int $uid = 0, $id)
    {
        $res = $this->dao->getOne(['id' => $id, 'uid' => $uid]);
        $res['order_info'] = null;
        $res['two'] = null;
        $res['one'] = null;
        $res['other'] = null;
        if (!empty($res)) {
            $res['order_info'] = app()->make(BpOrderServices::class)->getOne(['order_id' => $res['order_id']], '*');
            if ($res['target'] == 'export_preliminary') {
                $res['one'] = $res ? $res->toArray() : [];
                $res['two'] = $this->dao->getOne(['run_id' => $res['run_id'], 'target' => 'export_optimization']);
            } else if ($res['target'] == 'export_optimization') {
                $res['two'] = $res ? $res->toArray() : [];
                $res['one'] = $this->dao->getOne(['run_id' => $res['run_id'], 'target' => 'export_preliminary']);
            }
        }
        return $res ? $res->toArray() : [];
    }
}
