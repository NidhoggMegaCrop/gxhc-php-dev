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
use app\services\gxhc\MessageCenterServices;

/**
 * 消息中心用户端API
 * Class MessageCenterController
 * @package app\api\controller\v1\gxhc
 */
class MessageCenterController
{
    /**
     * @var MessageCenterServices
     */
    protected $services;

    /**
     * MessageCenterController constructor.
     * @param MessageCenterServices $services
     */
    public function __construct(MessageCenterServices $services)
    {
        $this->services = $services;
    }

    /**
     * 消息列表
     * @param Request $request
     * @return mixed
     */
    public function list(Request $request)
    {
        $uid = (int)$request->uid();
        $category = (int)$request->get('category', 0);
        return app('json')->success($this->services->getUserMessageList($uid, $category));
    }

    /**
     * 消息详情
     * @param Request $request
     * @param int $id
     * @return mixed
     */
    public function detail(Request $request, $id)
    {
        if (!$id) {
            return app('json')->fail(100100);
        }
        $uid = (int)$request->uid();
        return app('json')->success($this->services->getMessageDetail($uid, (int)$id));
    }

    /**
     * 未读消息数量（红点Badge）
     * @param Request $request
     * @return mixed
     */
    public function unreadCount(Request $request)
    {
        $uid = (int)$request->uid();
        return app('json')->success($this->services->getUnreadCount($uid));
    }

    /**
     * 标记消息已读
     * @param Request $request
     * @return mixed
     */
    public function markRead(Request $request)
    {
        $uid = (int)$request->uid();
        $data = $request->getMore([
            ['id', 0],
            ['category', 0]
        ]);
        $this->services->markAsRead($uid, (int)$data['id'], (int)$data['category']);
        return app('json')->success('操作成功');
    }

    /**
     * 删除消息
     * @param Request $request
     * @param int $id
     * @return mixed
     */
    public function delete(Request $request, $id)
    {
        if (!$id) {
            return app('json')->fail(100100);
        }
        $uid = (int)$request->uid();
        $this->services->deleteMessage($uid, (int)$id);
        return app('json')->success('删除成功');
    }
}
