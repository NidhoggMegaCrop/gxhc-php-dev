<?php

/**
 * @author: liaofei<136327134@qq.com>
 * @day: 2020/9/12
 */

namespace app\adminapi\controller;

use app\services\message\notice\{
    SmsService
};

use app\services\gxhc\BpRecordServices;

class Test
{
    public function index()
    {
        // 2. 触发直播申请通知
        // event('FeiShuListener', [
        //     [
        //         'applicant' => '李四',
        //         'project_name' => '产品发布会',
        //         'expected_date' => '2023-06-20',
        //         'expected_time' => '14:00-16:00',
        //         'apply_time' => '2023-06-20',
        //         'contact' => '15512341234'
        //     ],
        //     'feishu_apply_live'
        // ]);

        $NoticeSms = app()->make(SmsService::class);
        // $res = $NoticeSms->sendDx(true, 15574214151, [], 'bp_a');
        // var_dump($res);
        // echo 1;

        // $res =  sys_config('get_avatar', '');
        // $res =  sys_config('h5_avatar', '');
        // var_dump($res);

        $bpRecordServices = app()->make(BpRecordServices::class);
        $res = $bpRecordServices->bpStatusUpdate();
        
    }
}
