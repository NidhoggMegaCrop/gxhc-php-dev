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
namespace app\adminapi\controller\v1\gxhc;

use app\adminapi\controller\AuthController;
use app\services\system\config\SystemConfigServices;
use think\facade\App;
use crmeb\services\CacheService;

/**
 * 最新动态管理（简化版 - 使用系统配置存储）
 * Class News
 * @package app\adminapi\controller\v1\gxhc
 */
class News extends AuthController
{
    /**
     * 配置项名称
     */
    const CONFIG_NAME = 'gxhc_news';

    /**
     * SystemConfigServices
     * @var SystemConfigServices
     */
    protected $configServices;

    /**
     * 构造方法
     * News constructor.
     * @param App $app
     * @param SystemConfigServices $configServices
     */
    public function __construct(App $app, SystemConfigServices $configServices)
    {
        parent::__construct($app);
        $this->configServices = $configServices;
    }

    /**
     * 获取所有动态数据
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
     * 保存所有数据
     * @param array $data
     * @return bool
     */
    protected function saveAllData(array $data): bool
    {
        $this->configServices->update(['menu_name' => self::CONFIG_NAME], ['value' => json_encode($data)]);
        CacheService::clear();
        return true;
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
     * @return mixed
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['page', 1],
            ['limit', 10],
            ['status', ''],
            ['type', ''],
            ['keyword', ''],
            ['author', '']
        ]);

        $allData = $this->getAllData();

        // 过滤
        if ($where['status'] !== '') {
            $allData = array_filter($allData, function($item) use ($where) {
                return $item['status'] == $where['status'];
            });
        }
        if (!empty($where['type'])) {
            $type = $where['type'];
            $allData = array_filter($allData, function($item) use ($type) {
                return ($item['type'] ?? '') == $type;
            });
        }
        if (!empty($where['keyword'])) {
            $keyword = $where['keyword'];
            $allData = array_filter($allData, function($item) use ($keyword) {
                return strpos($item['title'] ?? '', $keyword) !== false || strpos($item['content'] ?? '', $keyword) !== false;
            });
        }
        if (!empty($where['author'])) {
            $author = $where['author'];
            $allData = array_filter($allData, function($item) use ($author) {
                return ($item['author'] ?? '') == $author;
            });
        }

        // 排序（按发布时间倒序）
        usort($allData, function($a, $b) {
            $timeA = $a['publish_time'] ?? $a['add_time'] ?? 0;
            $timeB = $b['publish_time'] ?? $b['add_time'] ?? 0;
            return $timeB - $timeA;
        });

        // 分页
        $total = count($allData);
        $page = max(1, (int)$where['page']);
        $limit = max(1, (int)$where['limit']);
        $list = array_slice(array_values($allData), ($page - 1) * $limit, $limit);

        // 添加格式化的时间
        foreach ($list as &$item) {
            $publishTime = $item['publish_time'] ?? $item['add_time'] ?? time();
            $item['time_ago'] = $this->formatTimeAgo($publishTime);
            $item['publish_time_format'] = date('Y-m-d H:i:s', $publishTime);
        }

        return app('json')->success([
            'list' => $list,
            'count' => $total
        ]);
    }

    /**
     * 获取所有启用的动态
     * @return mixed
     */
    public function getAllNews()
    {
        $type = $this->request->get('type', '');
        $limit = (int)$this->request->get('limit', 0);

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
     * @param int $id
     * @return mixed
     */
    public function read($id)
    {
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

    /**
     * 创建动态
     * @return mixed
     */
    public function save()
    {
        $data = $this->request->postMore([
            ['title', ''],
            ['content', ''],
            ['author', ''],
            ['type', 'news'],
            ['cover_image', ''],
            ['sort', 0],
            ['status', 1],
            ['publish_time', '']
        ]);

        if (empty($data['title'])) {
            return app('json')->fail('请填写标题');
        }
        if (empty($data['content'])) {
            return app('json')->fail('请填写内容');
        }

        $allData = $this->getAllData();

        // 生成新ID
        $maxId = 0;
        foreach ($allData as $item) {
            if (($item['id'] ?? 0) > $maxId) {
                $maxId = $item['id'];
            }
        }
        $data['id'] = $maxId + 1;
        $data['add_time'] = time();
        $data['update_time'] = time();
        $data['view_count'] = 0;

        // 处理发布时间
        if (!empty($data['publish_time'])) {
            $data['publish_time'] = strtotime($data['publish_time']);
        } else {
            $data['publish_time'] = time();
        }

        $allData[] = $data;
        $this->saveAllData($allData);

        return app('json')->success('创建成功');
    }

    /**
     * 更新动态
     * @param int $id
     * @return mixed
     */
    public function update($id)
    {
        if (!$id) {
            return app('json')->fail('参数错误');
        }

        $data = $this->request->postMore([
            ['title', ''],
            ['content', ''],
            ['author', ''],
            ['type', ''],
            ['cover_image', ''],
            ['sort', 0],
            ['status', 1],
            ['publish_time', '']
        ]);

        $allData = $this->getAllData();
        $found = false;

        foreach ($allData as $index => $item) {
            if ($item['id'] == $id) {
                // 处理发布时间
                if (!empty($data['publish_time']) && !is_numeric($data['publish_time'])) {
                    $data['publish_time'] = strtotime($data['publish_time']);
                }
                // 合并数据
                $allData[$index] = array_merge($item, array_filter($data, function($v) {
                    return $v !== '';
                }));
                $allData[$index]['update_time'] = time();
                $found = true;
                break;
            }
        }

        if (!$found) {
            return app('json')->fail('数据不存在');
        }

        $this->saveAllData($allData);
        return app('json')->success('更新成功');
    }

    /**
     * 删除动态
     * @param int $id
     * @return mixed
     */
    public function delete($id)
    {
        if (!$id) {
            return app('json')->fail('参数错误');
        }

        $allData = $this->getAllData();
        $newData = [];

        foreach ($allData as $item) {
            if ($item['id'] != $id) {
                $newData[] = $item;
            }
        }

        if (count($newData) == count($allData)) {
            return app('json')->fail('数据不存在');
        }

        $this->saveAllData($newData);
        return app('json')->success('删除成功');
    }

    /**
     * 修改状态
     * @param int $id
     * @return mixed
     */
    public function setStatus($id)
    {
        if (!$id) {
            return app('json')->fail('参数错误');
        }

        $status = $this->request->post('status', 1);

        $allData = $this->getAllData();
        $found = false;

        foreach ($allData as $index => $item) {
            if ($item['id'] == $id) {
                $allData[$index]['status'] = (int)$status;
                $allData[$index]['update_time'] = time();
                $found = true;
                break;
            }
        }

        if (!$found) {
            return app('json')->fail('数据不存在');
        }

        $this->saveAllData($allData);
        return app('json')->success('修改成功');
    }
}
