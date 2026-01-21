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
 * 优惠卷，砍价，拼团，秒杀 路由
 */
Route::group('marketing', function () {
    /** 积分活动 */
    Route::group(function () {
        //积分记录
        Route::get('point_record', 'v1.marketing.integral.StorePointRecord/pointRecord')->option(['real_name' => '积分记录列表']);
        Route::post('point_record/remark/:id', 'v1.marketing.integral.StorePointRecord/pointRecordRemark')->option(['real_name' => '积分记录列表备注']);
        Route::get('point/get_basic', 'v1.marketing.integral.StorePointRecord/getBasic')->option(['real_name' => '积分统计基本信息']);
        Route::get('point/get_trend', 'v1.marketing.integral.StorePointRecord/getTrend')->option(['real_name' => '积分统计趋势图']);
    })->option(['parent' => 'marketing', 'cate_name' => '积分活动']);
   
})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCheckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
])->option(['mark' => 'marketing', 'mark_name' => '营销活动']);
