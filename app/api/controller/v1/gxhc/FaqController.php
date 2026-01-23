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

use app\Request;

/**
 * 常见问题API（简化版 - 使用系统配置存储）
 * Class FaqController
 * @package app\api\controller\v1\gxhc
 */
class FaqController
{
    /**
     * 配置项名称
     */
    const CONFIG_NAME = 'gxhc_faq';

    /**
     * 获取所有数据
     * @return array
     */
    protected function getAllData(): array
    {
        $data = sys_config(self::CONFIG_NAME);
        if (empty($data) || !is_array($data)) {
            return [];
        }
        return $data;
    }

    /**
     * 获取常见问题列表
     * @param Request $request
     * @return mixed
     */
    public function getFaqList(Request $request)
    {
        $category = $request->get('category', '');
        $limit = (int)$request->get('limit', 20);

        $allData = $this->getAllData();

        // 只返回启用的
        $list = array_filter($allData, function($item) use ($category) {
            if (($item['status'] ?? 0) != 1) {
                return false;
            }
            if (!empty($category) && ($item['category'] ?? '') != $category) {
                return false;
            }
            return true;
        });

        // 排序
        usort($list, function($a, $b) {
            return ($b['sort'] ?? 0) - ($a['sort'] ?? 0);
        });

        $list = array_values($list);
        if ($limit > 0) {
            $list = array_slice($list, 0, $limit);
        }

        return app('json')->success($list);
    }

    /**
     * 获取所有分类
     * @param Request $request
     * @return mixed
     */
    public function getCategories(Request $request)
    {
        $allData = $this->getAllData();
        $categories = [];

        foreach ($allData as $item) {
            $category = $item['category'] ?? '';
            if (!empty($category) && ($item['status'] ?? 0) == 1 && !in_array($category, $categories)) {
                $categories[] = $category;
            }
        }

        return app('json')->success($categories);
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

        $allData = $this->getAllData();

        foreach ($allData as $item) {
            if ($item['id'] == $id) {
                return app('json')->success($item);
            }
        }

        return app('json')->fail('数据不存在');
    }
}
