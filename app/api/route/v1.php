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

use app\api\middleware\BlockerMiddleware;
use think\facade\Route;
use think\facade\Config;
use think\Response;

//定时任务调用接口
Route::get('crontab/run', 'v1.CrontabController/crontabRun')->name('crontabRun')->option(['real_name' => '定时任务调用接口']);
Route::get('test/test', 'v1.CrontabController/test')->name('crontabRun')->option(['real_name' => 'test']);

Route::group(function () {
    Route::any('pay/notify/:type', 'v1.PayController/notify')->option(['real_name' => '支付回调']); //支付回调
    Route::get('version', 'v1.PublicController/getVersion')->option(['real_name' => '获取代码版本号']);
})->middleware(\app\http\middleware\AllowOriginMiddleware::class)->option(['mark' => 'serve', 'mark_name' => '服务接口']);


Route::group(function () {
    //商城基础配置汇总接口
    Route::get('basic_config', 'v1.PublicController/getMallBasicConfig')->option(['real_name' => '商城基础配置汇总接口']);
})->middleware(\app\http\middleware\AllowOriginMiddleware::class)
    ->middleware(\app\api\middleware\StationOpenMiddleware::class)
    ->option(['mark' => 'base', 'mark_name' => '基础接口']);

//会员授权接口
Route::group(function () {
    Route::group(function () {
        //用户修改手机号
        // Route::post('user/updatePhone', 'v1.LoginController/update_binding_phone')->name('updateBindingPhone')->option(['real_name' => '用户修改手机号']);
        //设置登录code
        // Route::post('user/code', 'v1.user.StoreService/setLoginCode')->name('setLoginCode')->option(['real_name' => '设置登录code']);
        //查看code是否可用
        // Route::get('user/code', 'v1.LoginController/setLoginKey')->name('getLoginKey')->option(['real_name' => '查看code是否可用']);
        //用户绑定手机号
        // Route::post('user/binding', 'v1.LoginController/user_binding_phone')->name('userBindingPhone')->option(['real_name' => '用户绑定手机号']);
        Route::get('logout', 'v1.LoginController/logout')->name('logout')->option(['real_name' => '退出登录']); // 退出登录
        //公共类
        Route::post('upload/image', 'v1.PublicController/upload_image')->name('uploadImage')->option(['real_name' => '图片上传']); //图片上传
        Route::post('upload/pdf', 'v1.PublicController/upload_pdf')->name('upload_pdf')->option(['real_name' => 'pdf文件上传']);
        Route::post('upload/file', 'v1.PublicController/upload_file')->name('upload_file')->option(['real_name' => '文件上传']);
    })->option(['mark' => 'common', 'mark_name' => '公共接口']);

    Route::group(function () {
        //用户类  用户coupons/order
        Route::get('user', 'v1.user.UserController/user')->name('user')->option(['real_name' => '个人中心']); //个人中心
        Route::post('user/edit', 'v1.user.UserController/edit')->name('userEdit')->option(['real_name' => '用户修改信息']); //用户修改信息
        // Route::post('user/spread', 'v1.user.UserController/spread')->name('userSpread')->option(['real_name' => '静默绑定授权']);//静默绑定授权
        // Route::get('user/balance', 'v1.user.UserController/balance')->name('userBalance')->option(['real_name' => '用户资金统计']);//用户资金统计
        // Route::get('userinfo', 'v1.user.UserController/userinfo')->name('userinfo')->option(['real_name' => '用户信息']);// 用户信息
    })->option(['parent' => 'user', 'cate_name' => '用户中心']);

    Route::group(function () {
        //用户类  地址
        Route::get('address/detail/:id', 'v1.user.UserAddressController/address')->name('address')->option(['real_name' => '获取单个地址']); //获取单个地址
        Route::get('address/list', 'v1.user.UserAddressController/address_list')->name('addressList')->option(['real_name' => '地址列表']); //地址列表
        Route::post('address/default/set', 'v1.user.UserAddressController/address_default_set')->name('addressDefaultSet')->option(['real_name' => '设置默认地址']); //设置默认地址
        Route::get('address/default', 'v1.user.UserAddressController/address_default')->name('addressDefault')->option(['real_name' => '获取默认地址']); //获取默认地址
        Route::post('address/edit', 'v1.user.UserAddressController/address_edit')->name('addressEdit')->option(['real_name' => '修改/添加地址']); //修改 添加 地址
        Route::post('address/del', 'v1.user.UserAddressController/address_del')->name('addressDel')->option(['real_name' => '删除地址']); //删除地址
    })->option(['parent' => 'user', 'cate_name' => '用户地址']);

    Route::group(function () {
        //消息站内信
        Route::get('user/message_system/list', 'v1.user.MessageSystemController/message_list')->name('MessageSystemList')->option(['real_name' => '站内信列表']); //站内信列表
        Route::get('user/message_system/detail/:id', 'v1.user.MessageSystemController/detail')->name('MessageSystemDetail')->option(['real_name' => '详情']); //详情
        Route::get('user/message_system/edit_message', 'v1.user.MessageSystemController/edit_message')->name('EditMessage')->option(['real_name' => '站内信设置']); //站内信设为未读/删除
    })->option(['mark' => 'message_system', 'mark_name' => '站内信']);

    Route::group(function () {
        //商品类
        Route::post('image_base64', 'v1.PublicController/get_image_base64')->name('getImageBase64')->option(['real_name' => '获取图片base64']); // 获取图片base64
        Route::get('groom/list/:type', 'v1.store.StoreProductController/groom_list')->name('groomList')->option(['real_name' => '获取首页推荐不同类型商品的轮播图和商品']); //获取首页推荐不同类型商品的轮播图和商品
    })->option(['mark' => 'product', 'mark_name' => '商品']);

    Route::group(function () {
        //账单类
        Route::get('integral/list', 'v1.user.UserBillController/integral_list')->name('integralList')->option(['real_name' => '积分记录']); //积分记录
        Route::get('spread/banner', 'v1.user.UserBillController/spread_banner')->name('spreadBanner')->option(['real_name' => '推广分销二维码海报生成']); //推广分销二维码海报生成
        Route::get('user/spread_info', 'v1.user.UserBillController/getSpreadInfo')->name('getSpreadInfo')->option(['real_name' => '获取分销背景等信息']); //获取分销背景等信息
        Route::get('user/routine_code', 'v1.user.UserBillController/getRoutineCode')->name('getRoutineCode')->option(['real_name' => '小程序二维码']); //小程序二维码
    })->option(['mark' => 'division', 'mark_name' => '账单']);

    Route::group(function () {
        Route::post('createOrder', 'v1.gxhc.BpOrderController/createOrder')->name('createOrder')->option(['real_name' => '创建订单']);
        Route::post('pay', 'v1.gxhc.BpOrderController/pay')->name('pay')->option(['real_name' => '创建订单']);
        Route::get('userEnergy', 'v1.gxhc.UserEnergyController/userEnergy')->name('userEnergy')->option(['real_name' => '个人中心']); //个人中心
        Route::get('energyList', 'v1.gxhc.UserEnergyController/energyList')->name('energyList')->option(['real_name' => '个人中心']); //个人中心
        Route::post('runBp', 'v1.gxhc.BpRecordController/runBp')->name('runBp')->option(['real_name' => '个人中心']); //个人中心
        Route::get('getBpInfo', 'v1.gxhc.BpRecordController/getBpInfo')->name('getBpInfo')->option(['real_name' => '个人中心']); //个人中心
        Route::get('getBpResultList', 'v1.gxhc.BpRecordController/getBpResultList')->name('getBpResultList')->option(['real_name' => '个人中心']); //个人中心
        Route::get('getBpResultInfo', 'v1.gxhc.BpRecordController/getBpResultInfo')->name('getBpResultInfo')->option(['real_name' => '个人中心']); //个人中心
        Route::post('feedback', 'v1.gxhc.FeedBackController/feedback')->name('upload_file')->option(['real_name' => '文件上传']);

        // 兑换码相关接口
        Route::post('redemption/mint', 'v1.gxhc.RedemptionCodeController/mint')->name('redemptionMint')->option(['real_name' => '用户铸造兑换码']);
        Route::post('redemption/redeem', 'v1.gxhc.RedemptionCodeController/redeem')->name('redemptionRedeem')->option(['real_name' => '核销兑换码']);
        Route::get('redemption/myCodes', 'v1.gxhc.RedemptionCodeController/myMintedCodes')->name('redemptionMyCodes')->option(['real_name' => '我的兑换码列表']);
        Route::get('redemption/myUseCodes', 'v1.gxhc.RedemptionCodeController/myMintedUseCodes')->name('redemptionMyCodes')->option(['real_name' => '我的兑换码列表']);
        Route::get('redemption/energyInfo', 'v1.gxhc.RedemptionCodeController/energyInfo')->name('redemptionEnergyInfo')->option(['real_name' => '能量信息']);

        // 实战战报统计相关接口
        Route::get('battle_stats/list', 'v1.gxhc.BattleStatsController/getStats')->name('battleStatsList')->option(['real_name' => '获取战报统计数据']);
        Route::get('battle_stats/get/:key', 'v1.gxhc.BattleStatsController/getStatByKey')->name('battleStatsGetByKey')->option(['real_name' => '根据key获取统计项']);

        // 最新动态相关接口
        Route::get('news/list', 'v1.gxhc.NewsController/getNewsList')->name('newsList')->option(['real_name' => '获取最新动态列表']);
        Route::get('news/featured', 'v1.gxhc.NewsController/getFeaturedNews')->name('newsFeatured')->option(['real_name' => '获取精华动态']);
        Route::get('news/detail/:id', 'v1.gxhc.NewsController/getNewsDetail')->name('newsDetail')->option(['real_name' => '获取动态详情']);

        // 常见问题相关接口
        Route::get('faq/list', 'v1.gxhc.FaqController/getFaqList')->name('faqList')->option(['real_name' => '获取常见问题列表']);
        Route::get('faq/categories', 'v1.gxhc.FaqController/getCategories')->name('faqCategories')->option(['real_name' => '获取常见问题分类']);
        Route::get('faq/detail/:id', 'v1.gxhc.FaqController/getFaqDetail')->name('faqDetail')->option(['real_name' => '获取常见问题详情']);
    })->option(['mark' => 'gxhc', 'mark_name' => '国信合创接口']);

})->middleware(\app\http\middleware\AllowOriginMiddleware::class)
    ->middleware(\app\api\middleware\StationOpenMiddleware::class)
    ->middleware(\app\api\middleware\AuthTokenMiddleware::class, true);

//未授权接口
Route::group(function () {

    Route::group(function () {
        Route::get('get_colleges', 'v1.PublicController/get_colleges')->name('get_colleges')->option(['real_name' => '大学列表']);
        Route::get('get_enterprise', 'v1.PublicController/get_enterprise')->name('get_enterprise')->option(['real_name' => '企业列表']);
        Route::get('get_share', 'v1.gxhc.ShareController/get_share')->name('get_share')->option(['real_name' => '企业列表']);
        Route::get('share_set', 'v1.gxhc.ShareController/share_set')->name('get_share')->option(['real_name' => '企业列表']);
    })->option(['mark' => 'common', 'mark_name' => '公共接口']);

    Route::group(function () {
        Route::get('index/index', 'v1.index.IndexController/index')->option(['real_name' => '移动端首页']); //移动端首页
        // Route::get('index', 'v1.PublicController/index')->name('index')->option(['real_name' => '首页']); //首页
    })->option(['mark' => 'index', 'mark_name' => '主页接口']);

    Route::group(function () {
        Route::get('get_industry_categories/list', 'v1.PublicController/get_industry_categories')->option(['real_name' => '获取投资领域']);
        Route::get('get_industry_sub_categories/list', 'v1.PublicController/get_industry_sub_categories')->option(['real_name' => '获取投资领域子类']);
        Route::get('get_director_membe/details', 'v1.gxhc.DirectorMembeController/details')->option(['real_name' => '理事成员详情']);
        Route::get('get_director_membe/list', 'v1.gxhc.DirectorMembeController/list')->option(['real_name' => '理事成员列表']);
        Route::post('director_member/add', 'v1.gxhc.DirectorMembeController/add')->option(['real_name' => '添加理事会成员']);
        Route::post('invest_projects/save', 'v1.gxhc.InvestProjectsController/save')->option(['real_name' => '添加项目']);
        Route::post('invest_projects/update_score', 'v1.gxhc.InvestProjectsController/update_score')->option(['real_name' => '更新分数']);
        Route::get('invest_projects/details', 'v1.gxhc.InvestProjectsController/details')->option(['real_name' => '申请项目详情']);
        Route::post('invest_projects/revoke', 'v1.gxhc.InvestProjectsController/revoke')->option(['real_name' => '撤销项目']);
        Route::post('invest_projects/update_bp', 'v1.gxhc.InvestProjectsController/update_bp')->option(['real_name' => '更新bp']);
        Route::post('invest_projects/update_supply', 'v1.gxhc.InvestProjectsController/update_supply')->option(['real_name' => '更新商业技术书']);
        Route::post('user/auth', 'v1.gxhc.UserAuthController/auth')->option(['real_name' => '用户身份认证']);
        Route::post('user/realName', 'v1.user.UserController/realName')->option(['real_name' => '用户实名']);
        Route::get('user/getRealName', 'v1.user.UserController/getRealName')->option(['real_name' => '获取实名']);

        // 直播申请相关路由
        Route::post('apply_live/apply', 'v1.gxhc.ApplyLiveController/apply')->option(['real_name' => '申请直播']);
        Route::get('apply_live/user_live', 'v1.gxhc.ApplyLiveController/userLive')->option(['real_name' => '查询用户的直播']);
        Route::get('apply_live/booked_slots', 'v1.gxhc.ApplyLiveController/bookedSlots')->option(['real_name' => '获取直播申请详情']);

        Route::get('downloadBpUrl', 'v1.gxhc.BpRecordController/downloadBpUrl')->name('downloadBpUrl')->option(['real_name' => '个人中心']); //个人中心
    })->option(['mark' => 'gxhc', 'mark_name' => '国信合创接口']);

    Route::group(function () {
        //获取城市列表
        Route::get('city_list', 'v1.PublicController/city_list')->name('cityList')->option(['real_name' => '获取城市列表']);
    })->option(['mark' => 'setting', 'mark_name' => '商城配置']);

    Route::group(function () {
        Route::get('get_workerman_url', 'v1.PublicController/getWorkerManUrl')->name('getWorkerManUrl')->option(['real_name' => '长链接设置']);
        Route::get('user_agreement', 'v1.PublicController/getUserAgreement')->name('getUserAgreement')->option(['real_name' => '获取用户协议']);
        Route::get('get_agreement/:type', 'v1.PublicController/getAgreement')->name('getAgreement')->option(['real_name' => '获取协议']);
    })->option(['mark' => 'other', 'mark_name' => '其他接口']);

    Route::group(function () {
        //分享配置
        Route::get('share', 'v1.PublicController/share')->name('share')->option(['real_name' => '分享配置']); //分享配置
    })->option(['mark' => 'setting', 'mark_name' => '商城配置']);
})->middleware(\app\http\middleware\AllowOriginMiddleware::class)
    ->middleware(\app\api\middleware\StationOpenMiddleware::class)
    ->middleware(\app\api\middleware\AuthTokenMiddleware::class, false);

Route::miss(function () {
    if (app()->request->isOptions()) {
        $header = Config::get('cookie.header');
        unset($header['Access-Control-Allow-Credentials']);
        return Response::create('ok')->code(200)->header($header);
    } else
        return Response::create()->code(404);
});
