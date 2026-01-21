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
use think\facade\Route;

/**
 * 直播申请管理相关路由
 */
Route::group('apply_live', function () {
    //直播申请列表
    Route::get('list', 'v1.gxhc.ApplyLive/index')->option(['real_name' => '直播申请列表']);
    //直播申请详情
    Route::get('detail/:id', 'v1.gxhc.ApplyLive/read')->option(['real_name' => '直播申请详情']);
    //审核通过直播申请
    Route::post('approve/:id', 'v1.gxhc.ApplyLive/approve')->option(['real_name' => '审核通过直播申请']);
    //拒绝直播申请
    Route::post('reject/:id', 'v1.gxhc.ApplyLive/reject')->option(['real_name' => '拒绝直播申请']);
    //删除直播申请
    Route::delete('delete/:id', 'v1.gxhc.ApplyLive/delete')->option(['real_name' => '删除直播申请']);
    //修改直播申请状态
    Route::post('set_status/:id/:status', 'v1.gxhc.ApplyLive/set_status')->option(['real_name' => '修改直播申请状态']);
})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCheckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
])->option(['mark' => 'apply_live', 'mark_name' => '直播申请管理']);

Route::group('marketing', function () {
    //能量记录
    Route::get('energy_record', 'v1.gxhc.UserEnergyRecord/pointRecord')->option(['real_name' => '积分记录列表']);
    Route::post('energy_record/remark/:id', 'v1.gxhc.UserEnergyRecord/pointRecordRemark')->option(['real_name' => '积分记录列表备注']);
    Route::get('energy/get_basic', 'v1.gxhc.UserEnergyRecord/getBasic')->option(['real_name' => '积分统计基本信息']);
    Route::get('energy/get_trend', 'v1.gxhc.UserEnergyRecord/getTrend')->option(['real_name' => '积分统计趋势图']);
})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCheckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
])->option(['mark' => 'marketing', 'mark_name' => '营销活动']);


Route::group('bp', function () {
    Route::get('get_list', 'v1.gxhc.BpRecord/get_list')->option(['real_name' => '积分统计趋势图']);
    Route::get('get_order_list', 'v1.gxhc.BpOrder/get_list')->option(['real_name' => '积分统计趋势图']);
    Route::get('get_feedback_list', 'v1.gxhc.FeedBack/get_list')->option(['real_name' => '积分统计趋势图']);
    Route::get('get_share_list', 'v1.gxhc.ShareRecord/get_list')->option(['real_name' => '积分统计趋势图']);
    
})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCheckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
])->option(['mark' => 'bp', 'mark_name' => 'bp']);

/**
 * 兑换码管理相关路由
 */
Route::group('redemption', function () {
    // 兑换码列表
    Route::get('list', 'v1.gxhc.RedemptionCode/index')->option(['real_name' => '兑换码列表']);
    // 兑换码详情
    Route::get('detail/:id', 'v1.gxhc.RedemptionCode/read')->option(['real_name' => '兑换码详情']);
    // 系统批量生成兑换码
    Route::post('generate', 'v1.gxhc.RedemptionCode/generate')->option(['real_name' => '系统批量生成兑换码']);
    // 生成兑换码表单配置
    Route::get('generateForm', 'v1.gxhc.RedemptionCode/generateForm')->option(['real_name' => '生成兑换码表单配置']);
    // 修改兑换码状态
    Route::post('set_status/:id/:status', 'v1.gxhc.RedemptionCode/setStatus')->option(['real_name' => '修改兑换码状态']);
    // 删除兑换码
    Route::delete('delete/:id', 'v1.gxhc.RedemptionCode/delete')->option(['real_name' => '删除兑换码']);
    // 统计概览
    Route::get('overview', 'v1.gxhc.RedemptionCode/overview')->option(['real_name' => '兑换码统计概览']);
    // 渠道统计
    Route::get('channel_statistics', 'v1.gxhc.RedemptionCode/channelStatistics')->option(['real_name' => '渠道统计数据']);
    // 渠道代理报表
    Route::get('channel_agent_report', 'v1.gxhc.RedemptionCode/channelAgentReport')->option(['real_name' => '渠道代理报表']);
    // 渠道列表
    Route::get('channel_list', 'v1.gxhc.RedemptionCode/channelList')->option(['real_name' => '渠道列表']);
    // 导出兑换码
    Route::get('export', 'v1.gxhc.RedemptionCode/export')->option(['real_name' => '导出兑换码']);
    Route::post('create', 'v1.gxhc.RedemptionCode/created')->option(['real_name' => '生成兑换码']);
})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCheckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
])->option(['mark' => 'redemption', 'mark_name' => '兑换码管理']);

/**
 * 实战战报统计管理相关路由
 */
Route::group('battle_stats', function () {
    // 战报统计列表
    Route::get('list', 'v1.gxhc.BattleStats/index')->option(['real_name' => '战报统计列表']);
    // 获取所有启用的统计项
    Route::get('all', 'v1.gxhc.BattleStats/getAllStats')->option(['real_name' => '获取所有启用的战报统计项']);
    // 战报统计详情
    Route::get('detail/:id', 'v1.gxhc.BattleStats/read')->option(['real_name' => '战报统计详情']);
    // 创建战报统计项
    Route::post('create', 'v1.gxhc.BattleStats/save')->option(['real_name' => '创建战报统计项']);
    // 更新战报统计项
    Route::put('update/:id', 'v1.gxhc.BattleStats/update')->option(['real_name' => '更新战报统计项']);
    // 删除战报统计项
    Route::delete('delete/:id', 'v1.gxhc.BattleStats/delete')->option(['real_name' => '删除战报统计项']);
    // 更新统计值
    Route::put('update_value/:id', 'v1.gxhc.BattleStats/updateValue')->option(['real_name' => '更新战报统计值']);
    // 修改状态
    Route::put('set_status/:id', 'v1.gxhc.BattleStats/setStatus')->option(['real_name' => '修改战报统计状态']);
})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCheckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
])->option(['mark' => 'battle_stats', 'mark_name' => '实战战报统计管理']);