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

use app\services\gxhc\DirectorMembeServices;
use crmeb\services\CacheService;
use app\Request;

class DirectorMembeController
{
    protected $services = NUll;

    /**
     * DirectorMembeController constructor.
     * @param DirectorMembeServices $services
     */
    public function __construct(DirectorMembeServices $services)
    {
        $this->services = $services;
    }

    public function list(Request $request)
    {
        list($id) = $request->getMore([
            ['id', 0],
        ], true);
        return app('json')->success($this->services->list($id));
    }

    public function details(Request $request)
    {
        list($id) = $request->getMore([
            ['id', 0],
        ], true);
        return app('json')->success($this->services->details($id));
    }

    // 添加理事会成员
    public function add(Request $request)
    {
        $data = $request->postMore([
            ['name', ''],
            ['gender', ''],
            ['directorType', ''],
            ['phone', ''],
            ['city', ''],
            ['address', ''],
            ['position', ''],
            ['title', ''],
            ['major', ''],
            ['photo', ''],
            ['introduction', ''],
            ['status', 'normal'],
            ['remark', ''],
            ['joinDate', ''],
        ]);

        // 验证必要字段
        if (empty($data['name'])) {
            return app('json')->fail('姓名不能为空');
        }

        // 设置创建时间
        $data['add_time'] = time();

        // 调用服务层保存数据
        $result = $this->services->save($data);

        if ($result) {
            return app('json')->success('添加成功');
        } else {
            return app('json')->fail('添加失败');
        }
    }
}
