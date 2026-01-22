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

use app\services\gxhc\NewsServices;
use app\Request;

/**
 * 最新动态API
 * Class NewsController
 * @package app\api\controller\v1\gxhc
 */
class NewsController
{
    protected $services = NULL;

    /**
     * NewsController constructor.
     * @param NewsServices $services
     */
    public function __construct(NewsServices $services)
    {
        $this->services = $services;
    }

    /**
     * 获取最新动态列表
     * @param Request $request
     * @return mixed
     */
    public function getNewsList(Request $request)
    {
        try {
            $type = $request->get('type', '');
            $limit = (int)$request->get('limit', 20);

            $list = $this->services->getAllNews($type, $limit);

            return app('json')->success($list);
        } catch (\Exception $e) {
            return app('json')->fail($e->getMessage());
        }
    }

    /**
     * 获取精华动态（直播实战精华）
     * @param Request $request
     * @return mixed
     */
    public function getFeaturedNews(Request $request)
    {
        try {
            $limit = (int)$request->get('limit', 10);

            $list = $this->services->getAllNews('featured', $limit);

            return app('json')->success($list);
        } catch (\Exception $e) {
            return app('json')->fail($e->getMessage());
        }
    }

    /**
     * 获取动态详情
     * @param Request $request
     * @return mixed
     */
    public function getNewsDetail(Request $request)
    {
        $id = (int)$request->param('id', 0);

        if (!$id) {
            return app('json')->fail('参数错误');
        }

        try {
            // 获取详情并增加浏览次数
            $info = $this->services->getInfo($id, true);
            return app('json')->success($info);
        } catch (\Exception $e) {
            return app('json')->fail($e->getMessage());
        }
    }
}
