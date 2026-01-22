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

use app\services\gxhc\FaqServices;
use app\Request;

/**
 * 常见问题API
 * Class FaqController
 * @package app\api\controller\v1\gxhc
 */
class FaqController
{
    protected $services = NULL;

    /**
     * FaqController constructor.
     * @param FaqServices $services
     */
    public function __construct(FaqServices $services)
    {
        $this->services = $services;
    }

    /**
     * 获取常见问题列表
     * @param Request $request
     * @return mixed
     */
    public function getFaqList(Request $request)
    {
        try {
            $category = $request->get('category', '');
            $limit = (int)$request->get('limit', 20);

            $list = $this->services->getAllFaq($category, $limit);

            return app('json')->success($list);
        } catch (\Exception $e) {
            return app('json')->fail($e->getMessage());
        }
    }

    /**
     * 获取所有分类
     * @param Request $request
     * @return mixed
     */
    public function getCategories(Request $request)
    {
        try {
            $categories = $this->services->getAllCategories();
            return app('json')->success($categories);
        } catch (\Exception $e) {
            return app('json')->fail($e->getMessage());
        }
    }

    /**
     * 获取常见问题详情
     * @param Request $request
     * @return mixed
     */
    public function getFaqDetail(Request $request)
    {
        $id = (int)$request->param('id', 0);

        if (!$id) {
            return app('json')->fail('参数错误');
        }

        try {
            // 获取详情并增加查看次数
            $info = $this->services->getInfo($id, true);
            return app('json')->success($info);
        } catch (\Exception $e) {
            return app('json')->fail($e->getMessage());
        }
    }
}
