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
 * 实战战报统计管理（简化版 - 使用系统配置存储）
 * Class BattleStats
 * @package app\adminapi\controller\v1\gxhc
 */
class BattleStats extends AuthController
{
    /**
     * 配置项名称
     */
    const CONFIG_NAME = 'gxhc_battle_stats';

    /**
     * SystemConfigServices
     * @var SystemConfigServices
     */
    protected $configServices;

    /**
     * 构造方法
     * BattleStats constructor.
     * @param App $app
     * @param SystemConfigServices $configServices
     */
    public function __construct(App $app, SystemConfigServices $configServices)
    {
        parent::__construct($app);
        $this->configServices = $configServices;
    }

    /**
     * 获取所有战报统计数据
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
     * 获取战报统计列表
     * @return mixed
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['page', 1],
            ['limit', 10],
            ['status', ''],
            ['keyword', '']
        ]);

        $allData = $this->getAllData();

        // 过滤
        if ($where['status'] !== '') {
            $allData = array_filter($allData, function($item) use ($where) {
                return $item['status'] == $where['status'];
            });
        }
        if (!empty($where['keyword'])) {
            $keyword = $where['keyword'];
            $allData = array_filter($allData, function($item) use ($keyword) {
                return strpos($item['name'], $keyword) !== false || strpos($item['key'], $keyword) !== false;
            });
        }

        // 排序
        usort($allData, function($a, $b) {
            return ($b['sort'] ?? 0) - ($a['sort'] ?? 0);
        });

        // 分页
        $total = count($allData);
        $page = max(1, (int)$where['page']);
        $limit = max(1, (int)$where['limit']);
        $list = array_slice(array_values($allData), ($page - 1) * $limit, $limit);

        return app('json')->success([
            'list' => $list,
            'count' => $total
        ]);
    }

    /**
     * 获取所有启用的统计项（用于前端展示）
     * @return mixed
     */
    public function getAllStats()
    {
        $allData = $this->getAllData();

        // 只返回启用的
        $list = array_filter($allData, function($item) {
            return ($item['status'] ?? 0) == 1;
        });

        // 排序
        usort($list, function($a, $b) {
            return ($b['sort'] ?? 0) - ($a['sort'] ?? 0);
        });

        return app('json')->success(array_values($list));
    }

    /**
     * 获取战报统计详情
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
                return app('json')->success($item);
            }
        }

        return app('json')->fail('数据不存在');
    }

    /**
     * 创建战报统计项
     * @return mixed
     */
    public function save()
    {
        $data = $this->request->postMore([
            ['key', ''],
            ['name', ''],
            ['value', 0],
            ['unit', ''],
            ['description', ''],
            ['sort', 0],
            ['icon', ''],
            ['status', 1]
        ]);

        if (empty($data['key'])) {
            return app('json')->fail('请填写统计项标识');
        }
        if (empty($data['name'])) {
            return app('json')->fail('请填写统计项名称');
        }

        $allData = $this->getAllData();

        // 检查key是否已存在
        foreach ($allData as $item) {
            if ($item['key'] == $data['key']) {
                return app('json')->fail('统计项标识已存在');
            }
        }

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

        $allData[] = $data;
        $this->saveAllData($allData);

        return app('json')->success('创建成功');
    }

    /**
     * 更新战报统计项
     * @param int $id
     * @return mixed
     */
    public function update($id)
    {
        if (!$id) {
            return app('json')->fail('参数错误');
        }

        $data = $this->request->postMore([
            ['key', ''],
            ['name', ''],
            ['value', 0],
            ['unit', ''],
            ['description', ''],
            ['sort', 0],
            ['icon', ''],
            ['status', 1]
        ]);

        $allData = $this->getAllData();
        $found = false;

        foreach ($allData as $index => $item) {
            if ($item['id'] == $id) {
                // 检查key是否与其他项重复
                if (!empty($data['key'])) {
                    foreach ($allData as $checkIndex => $checkItem) {
                        if ($checkIndex != $index && $checkItem['key'] == $data['key']) {
                            return app('json')->fail('统计项标识已存在');
                        }
                    }
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
     * 删除战报统计项
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
     * 修改统计值
     * @param int $id
     * @return mixed
     */
    public function updateValue($id)
    {
        if (!$id) {
            return app('json')->fail('参数错误');
        }

        $value = $this->request->post('value', 0);

        $allData = $this->getAllData();
        $found = false;

        foreach ($allData as $index => $item) {
            if ($item['id'] == $id) {
                $allData[$index]['value'] = (int)$value;
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
