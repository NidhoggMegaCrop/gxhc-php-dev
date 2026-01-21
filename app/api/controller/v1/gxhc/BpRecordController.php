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

namespace app\api\controller\v1\gxhc;

use app\services\gxhc\BpRecordServices;
use app\services\user\UserServices;
use app\services\gxhc\UserEnergyServices;
use app\services\gxhc\ShareRecordServices;
use crmeb\services\CacheService;
use app\Request;
use app\common\BpAgent;
use crmeb\utils\Json;

class BpRecordController
{
    protected $services = NULL;

    /**
     * BpRecordController constructor.
     * @param BpRecordServices $services
     */
    public function __construct(BpRecordServices $services)
    {
        $this->services = $services;
    }

    /**
     * 调用外部API运行BP分析
     *
     * @param Request $request
     * @return mixed
     */
    public function runBp(Request $request)
    {
        $uid = (int)$request->uid();
        // 获取上传的文件和其他参数
        $file = $request->file('files');
        $pipeline = $request->param('pipeline', 'bp_diagnosis');
        $target = $request->param('target', 'export_preliminary');
        $runId = $request->param('run_id', '');
        $fileName = $request->param('filename', '');

        if (!$file) {
            return json(['code' => 400, 'message' => '缺少文件参数']);
        }

        // 获取文件信息，适配ThinkPHP框架
        $filePath = $file->getRealPath();
        $fileOriginalName = $file->getOriginalName();  // ThinkPHP中获取原始文件名的方法
        $mimeType = $file->getMime();

        // 调用公共静态类处理API请求
        $result = BpAgent::runBp(
            $filePath,
            $fileName,
            $mimeType,
            $pipeline,
            $target,
            $runId
        );

        if ($result['code'] == 200) {
            $run_id = !empty($result['data']['id']) ? $result['data']['id'] : '';
            $status = !empty($result['data']['status']) ? $result['data']['status'] : 'QUEUED';
            if ($run_id) {
                $bp_id = $this->services->saveBpRecord($uid, [
                    'run_id' => $run_id,
                    'pipeline' => $pipeline,
                    'target' => $target,
                    'origina_filename' => $fileName,
                    'filename' => $fileName,
                    'status' => $status,
                    'add_time' => time(),
                    'run_response' => json_encode($result['data'])
                ]);

                // 邀请5个用户并上传bp奖励299
                $userService = app()->make(UserServices::class);
                $currer_user = $userService->getUserInfo($uid);
                if ($currer_user['spread_uid'] && !empty($currer_user)) {
                    $invite_uid = (int)$currer_user['spread_uid'];
                    $shareRecordServices = app()->make(ShareRecordServices::class);
                    $isNew = $shareRecordServices->getInviteCountByUid($invite_uid, 1, 2);
                    if (!$isNew) {
                        // 判断当前用户已经邀请的人数
                        $inviteCount = $shareRecordServices->getInviteCountByUid($invite_uid, 1, 1);
                        if ($inviteCount >= 5) {
                            // 修改状态为1的纪录为2
                            $shareRecordServices->updateRecordState($invite_uid, 1);
                            // 299元积分奖励
                            $userEnergyServices = app()->make(UserEnergyServices::class);
                            $exp_num = 299;
                            $spread_user = $userService->getUserInfo($invite_uid);
                            if ($exp_num) {
                                $userService->bcInc($invite_uid, 'energy', $exp_num, 'uid');
                                $now_energy = $spread_user['energy'] + $exp_num;
                                $userEnergyServices->income('get_user_energy', $invite_uid, $exp_num, $now_energy, 1);
                            }
                        }
                        $shareData = [
                            'uid' => $uid,
                            'invite_uid' => $invite_uid,
                            'is_new' => 1,
                            'add_time' => time()
                        ];
                        $shareRecordServices->saveRecord($shareData);
                    }
                }
                return app('json')->success('上传成功～', [
                    'run_id' => $run_id,
                    'bp_id' => $bp_id
                ]);
            }
        }

        return app('json')->fail('检测到您上传的⽂档似乎不是⼀份完整的商业计划书，⽆法提取有效信息，请检查⽂件是否正确。');
    }

    /**
     * 获取BP运行信息
     *
     * @param Request $request
     * @return mixed
     */
    public function getBpInfo(Request $request)
    {
        $uid = (int)$request->uid();
        $runId = $request->param('run_id', '');
        $target = $request->param('target', 'export_preliminary');
        if (empty($runId)) {
            return app('json')->fail('缺少run_id参数');
        }

        // 先查询bp 如果progress!=0 就请求更新数据
        $bpInfo = $this->services->getBpInfo($uid, $runId, $target);

        if (!$bpInfo) {
            return app('json')->fail('获取信息失败');
        }

        if ($bpInfo['status'] == 'SUCCEEDED') {
            return app('json')->success($bpInfo);
        }

        // 1.查询详情接口 更新进度
        $result = BpAgent::getRunInfo($runId);

        if ($result['code'] == 200) {
            $bpInfo['status'] = !empty($result['data']['status']) ? $result['data']['status'] : '';
            $bpInfo['progress'] = !empty($result['data']['progress']) ? $result['data']['progress'] : 0;
            $bpInfo['file_path'] = !empty($result['data']['input_files']['files'][0]['file_path']) ? $result['data']['input_files']['files'][0]['file_path'] : '';
            $this->services->updateBpInfo([
                'target' => $bpInfo['target'],
                'run_id' => $runId,
                'uid' => $uid,
            ], [
                'status' => $bpInfo['status'],
                'file_path' => $bpInfo['file_path'],
                'last_time' => time(),
                'progress' => $bpInfo['progress']
            ]);
        }

        if ($bpInfo['progress'] >= 100) {
            // 2.查询文件下载地址 file_id
            $result2 = BpAgent::getTaskInfo($runId, $bpInfo['target']);

            if ($result2['code'] == 200) {
                $re_status2 = !empty($result2['data'][0]['status']) ? $result2['data'][0]['status'] : 'QUEUED';
                $upData = [
                    'info_response' => json_encode($result2['data']),
                    'last_time' => time(),
                    'status' => $re_status2,
                ];
                if ($re_status2 == 'SUCCEEDED') {
                    $bpInfo['file_id'] = !empty($result2['data'][0]['output_data']['additional_files'][0]['file_id']) ? $result2['data'][0]['output_data']['additional_files'][0]['file_id'] : '';
                    $bpInfo['filename'] = !empty($result2['data'][0]['output_data']['additional_files'][0]['filename']) ? $result2['data'][0]['output_data']['additional_files'][0]['filename'] : '';
                    $bpInfo['file_path'] = !empty($result2['data'][0]['output_data']['additional_files'][0]['file_path']) ? $result2['data'][0]['output_data']['additional_files'][0]['file_path'] : '';
                    $upData['file_id'] = $bpInfo['file_id'];
                    $upData['filename'] = $bpInfo['filename'];
                    $upData['file_path'] = $bpInfo['file_path'];
                }
                $this->services->updateBpInfo([
                    'target' => $bpInfo['target'],
                    'run_id' => $runId,
                    'uid' => $uid,
                ], $upData);
            }
        }
        // var_dump($result);
        // var_dump($result2);

        return app('json')->success($bpInfo);
    }

    public function getBpInfo2(Request $request)
    {
        $uid = (int)$request->uid();
        $runId = $request->param('run_id', '');

        if (empty($runId)) {
            return app('json')->fail('缺少run_id参数');
        }

        // 先查询bp 如果progress!=0 就请求更新数据
        $bpInfo = $this->services->getBpInfo($uid, $runId);
        if ($bpInfo && $bpInfo['progress'] == 100) {
            $bpInfo['origina_file_path'] = $bpInfo['file_path'];
            if (empty($bpInfo['file_id'])) {
                $bpInfo['file_id'] = $this->getTaskInfo($uid, $runId, 'export_preliminary');
            }
            return app('json')->success($bpInfo);
        }

        // 调用公共静态类处理API请求
        $result = BpAgent::getRunInfo($runId);

        if ($result['code'] == 200) {
            $file_path = !empty($result['data']['input_files']['files'][0]['file_path']) ? $result['data']['input_files']['files'][0]['file_path'] : '';
            $filename = !empty($result['data']['input_files']['files'][0]['filename']) ? $result['data']['input_files']['files'][0]['filename'] : '';
            $progress = !empty($result['data']['progress']) ? $result['data']['progress'] : 0;
            $this->services->updateBpInfo([
                'run_id' => $runId,
                'uid' => $uid,
            ], [
                'origina_file_path' => $file_path,
                'progress' => $progress,
                'last_time' => time()
            ]);
            $resInfo = $bpInfo;
            $resInfo['origina_file_path'] = $file_path;
            $resInfo['origina_filename'] = $filename;
            $resInfo['progress'] = $progress;

            if ($progress == 100) {
                // 调用公共静态类处理API请求获取任务信息
                $resInfo['file_id'] = $this->getTaskInfo($uid, $runId, 'export_preliminary');
            }

            return app('json')->success($resInfo);
        } else {
            return app('json')->fail('获取信息失败');
        }
    }

    /**
     * 下载BP文件
     *
     * @param Request $request
     * @return mixed
     */
    public function downloadBpUrl(Request $request)
    {
        $uid = (int)$request->uid();
        $file_id = $request->param('file_id', '');

        if (empty($file_id)) {
            return app('json')->fail('缺少file_id参数');
        }

        // 调用公共静态类处理API请求下载文件
        $result = BpAgent::downloadFile($file_id);

        if ($result['code'] == 200) {
            // 获取文件内容和响应头
            $fileContent = $result['body'];
            $responseHeaders = $result['headers'];

            // 提取文件名
            $filename = 'bp_result.pdf'; // 默认文件名
            if (isset($responseHeaders['content-disposition'])) {
                $contentDisposition = $responseHeaders['content-disposition'];
                // 解析filename*=utf-8''...格式
                if (preg_match('/filename\*=utf-8\'\'([^;]+)/i', $contentDisposition, $matches)) {
                    $filename = urldecode($matches[1]);
                } elseif (preg_match('/filename=([^;]+)/i', $contentDisposition, $matches)) {
                    $filename = trim($matches[1], '"\'');
                }
            }

            // 设置响应头
            header('Content-Type: ' . ($responseHeaders['content-type'] ?? 'application/octet-stream'));
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . strlen($fileContent));

            // 输出文件内容
            echo $fileContent;
            exit;
        } else {
            return app('json')->fail('文件下载失败');
        }
    }

    public function getBpResultList(Request $request)
    {
        $uid = (int)$request->uid();
        return app('json')->success($this->services->getBpResultList($uid));
    }

    public function getBpResultInfo(Request $request)
    {
        $uid = (int)$request->uid();
        $id = $request->param('id', '');
        return app('json')->success($this->services->getBpResultInfo($uid, $id));
    }
}
