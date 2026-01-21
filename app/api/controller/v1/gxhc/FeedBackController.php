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

use app\services\gxhc\FeedBackServices;
use crmeb\services\CacheService;
use app\Request;

class FeedBackController
{
    protected $services = NUll;

    /**
     * FeedBackController constructor.
     * @param FeedBackServices $services
     */
    public function __construct(FeedBackServices $services)
    {
        $this->services = $services;
    }

    public function feedback(Request $request)
    {
        // 获取当前用户ID
        $uid = (int)$request->uid();

        // 获取反馈类型和内容
        $type = $request->post('category', '');
        $content = $request->post('content', '');

        // 参数验证
        if (empty($type)) {
            return app('json')->fail('请选择反馈类型');
        }

        if (empty($content)) {
            return app('json')->fail('请输入反馈内容');
        }

        // 准备插入数据
        $data = [
            'uid' => $uid,
            'type' => $type,
            'content' => $content,
            'status' => 0, // 默认状态为未处理
            'add_time' => time(),
            'last_time' => time()
        ];

        // 调用服务层保存反馈
        $result = $this->services->save($data);

        if ($result) {
            return app('json')->success('反馈提交成功');
        } else {
            return app('json')->fail('反馈提交失败');
        }
    }
}
