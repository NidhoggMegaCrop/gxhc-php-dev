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
 * 最新动态API（简化版 - 使用系统配置存储）
 * Class NewsController
 * @package app\api\controller\v1\gxhc
 */
class NewsController
{
    /**
     * 配置项名称
     */
    const CONFIG_NAME = 'gxhc_news';

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
     * 格式化相对时间
     * @param int $timestamp
     * @return string
     */
    protected function formatTimeAgo(int $timestamp): string
    {
        $diff = time() - $timestamp;

        if ($diff < 60) {
            return '刚刚';
        } elseif ($diff < 3600) {
            return floor($diff / 60) . '分钟前';
        } elseif ($diff < 86400) {
            return floor($diff / 3600) . '小时前';
        } elseif ($diff < 2592000) {
            return floor($diff / 86400) . '天前';
        } elseif ($diff < 31536000) {
            return floor($diff / 2592000) . '个月前';
        } else {
            return floor($diff / 31536000) . '年前';
        }
    }

    /**
     * 获取最新动态列表
     * @param Request $request
     * @return mixed
     */
    public function getNewsList(Request $request)
    {
        $type = $request->get('type', '');
        $limit = (int)$request->get('limit', 20);

        $allData = $this->getAllData();

        // 只返回启用的
        $list = array_filter($allData, function($item) use ($type) {
            if (($item['status'] ?? 0) != 1) {
                return false;
            }
            if (!empty($type) && ($item['type'] ?? '') != $type) {
                return false;
            }
            return true;
        });

        // 排序（按发布时间倒序）
        usort($list, function($a, $b) {
            $timeA = $a['publish_time'] ?? $a['add_time'] ?? 0;
            $timeB = $b['publish_time'] ?? $b['add_time'] ?? 0;
            return $timeB - $timeA;
        });

        $list = array_values($list);
        if ($limit > 0) {
            $list = array_slice($list, 0, $limit);
        }

        // 添加格式化的时间
        foreach ($list as &$item) {
            $publishTime = $item['publish_time'] ?? $item['add_time'] ?? time();
            $item['time_ago'] = $this->formatTimeAgo($publishTime);
        }

        return app('json')->success($list);
    }

    /**
     * 获取精华动态（直播实战精华）
     * @param Request $request
     * @return mixed
     */
    public function getFeaturedNews(Request $request)
    {
        $limit = (int)$request->get('limit', 10);

        $allData = $this->getAllData();

        // 只返回启用的且类型为featured
        $list = array_filter($allData, function($item) {
            return ($item['status'] ?? 0) == 1 && ($item['type'] ?? '') == 'featured';
        });

        // 排序
        usort($list, function($a, $b) {
            $timeA = $a['publish_time'] ?? $a['add_time'] ?? 0;
            $timeB = $b['publish_time'] ?? $b['add_time'] ?? 0;
            return $timeB - $timeA;
        });

        $list = array_values($list);
        if ($limit > 0) {
            $list = array_slice($list, 0, $limit);
        }

        // 添加格式化的时间
        foreach ($list as &$item) {
            $publishTime = $item['publish_time'] ?? $item['add_time'] ?? time();
            $item['time_ago'] = $this->formatTimeAgo($publishTime);
        }

        return app('json')->success($list);
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

        $allData = $this->getAllData();

        foreach ($allData as $item) {
            if ($item['id'] == $id) {
                $publishTime = $item['publish_time'] ?? $item['add_time'] ?? time();
                $item['time_ago'] = $this->formatTimeAgo($publishTime);
                $item['publish_time_format'] = date('Y-m-d H:i:s', $publishTime);
                return app('json')->success($item);
            }
        }

        return app('json')->fail('数据不存在');
    }
}
