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

namespace app\listener\notice;

use crmeb\interfaces\ListenerInterface;
use think\facade\Log;

/**
 * 飞书通知事件监听器
 * Class FeiShuListener
 * @package app\listener\notice
 */
class FeiShuListener implements ListenerInterface
{
    /**
     * 事件处理
     * @param $event
     * @return void
     */
    public function handle($event): void
    {
        try {
            [$data, $mark] = $event;
            
            /** @var \app\common\FeiShu $feishu */
            $feishu = app()->make(\app\common\FeiShu::class);
            
            if ($mark) {
                switch ($mark) {
                    // 直播申请通知
                    case 'feishu_apply_live':
                        $this->handleApplyLive($feishu, $data);
                        break;
                        
                    // 自定义通知
                    case 'feishu_custom_notification':
                        $this->handleCustomNotification($feishu, $data);
                        break;
                }
            }
        } catch (\Throwable $e) {
            // 记录错误日志
            Log::error('飞书通知事件处理异常: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    /**
     * 处理直播申请通知
     * @param \app\common\FeiShu $feishu
     * @param array $data
     * @return void
     */
    private function handleApplyLive(\app\common\FeiShu $feishu, array $data): void
    {
        $applicant = $data['applicant'] ?? '';
        $projectName = $data['project_name'] ?? '';
        $expectedDate = $data['expected_date'] ?? '';
        $expectedTime = $data['expected_time'] ?? '';
        $applyTime = $data['apply_time'] ?? date('Y-m-d H:i:s');
        $contact = $data['contact'] ?? '';
        
        $content = "申请人：{$applicant}\n" .
                  "项目名称：{$projectName}\n" .
                  "期望日期：{$expectedDate}\n" .
                  "期望时间：{$expectedTime}\n" .
                  "申请时间：{$applyTime}\n" .
                  "联系方式：{$contact}\n" .
                  "<font color='blue'>@15574214151</font>";
                  
        // 使用 FeiShu 类中实际存在的方法
        $feishu->sendNotification($content, 'interactive', [
            'title' => '🎥 一对一诊断直播申请通知'
        ]);
    }
    
    /**
     * 处理自定义通知
     * @param \app\common\FeiShu $feishu
     * @param array $data
     * @return void
     */
    private function handleCustomNotification(\app\common\FeiShu $feishu, array $data): void
    {
        $title = $data['title'] ?? '通知';
        $content = $data['content'] ?? '';
        $type = $data['type'] ?? 'text';
        
        // 使用 FeiShu 类中实际存在的方法
        switch ($type) {
            case 'text':
                $feishu->sendNotification($content, 'text');
                break;
                
            case 'post':
                $feishu->sendNotification($content, 'post', [
                    'title' => $title
                ]);
                break;
                
            case 'interactive':
                $feishu->sendNotification($content, 'interactive', [
                    'title' => $title,
                    'button_text' => $data['button_text'] ?? '查看详情',
                    'button_url' => $data['button_url'] ?? ''
                ]);
                break;
                
            default:
                $feishu->sendNotification($content, 'text');
        }
    }
}