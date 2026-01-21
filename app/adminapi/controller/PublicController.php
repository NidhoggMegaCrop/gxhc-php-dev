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

namespace app\adminapi\controller;


use app\Request;
use app\services\system\attachment\SystemAttachmentServices;
use app\services\system\SystemRouteServices;
use crmeb\services\CacheService;
use think\Response;

class PublicController
{

    /**
     * 下载文件
     * @param string $key
     * @return Response|\think\response\File
     */
    public function download(string $key = '')
    {
        /**
         * 根据给定的key从缓存中获取文件名和路径，并下载该文件
         *
         * @param string $key 缓存中存储文件名和路径的key
         * @return mixed 返回下载文件的响应或错误响应
         */
        if (!$key) {
            // 如果文件名和路径不存在或文件不存在，则返回500错误响应
            return Response::create()->code(500);
        }
        // 从缓存中获取文件名和路径
        $fileName = CacheService::get($key);
        if (is_array($fileName) && isset($fileName['path']) && isset($fileName['fileName']) && $fileName['path'] && $fileName['fileName'] && file_exists($fileName['path'])) {
            // 如果文件名和路径都存在且文件存在，则删除缓存并下载文件
            CacheService::delete($key);
            return download($fileName['path'], $fileName['fileName']);//
        }
        // 如果文件名和路径不存在或文件不存在，则返回500错误响应
        return Response::create()->code(500);

    }

    /**
     * 获取workerman请求域名
     * @return mixed
     */
    public function getWorkerManUrl()
    {
        /**
         * 获取 WorkerMan 服务器地址并返回 JSON 格式的成功响应
         *
         * @return \Illuminate\Http\JsonResponse
         */
        return app('json')->success(getWorkerManUrl());
    }

    /**
     * 扫码上传
     * @param Request $request
     * @param int $upload_type
     * @param int $type
     * @return Response
     * @author 吴汐
     * @email 442384644@qq.com
     * @date 2023/06/13
     */
    public function scanUpload(Request $request, $upload_type = 0, $type = 0)
    {
            /**
             * 上传文件
             *
             * @param Request $request HTTP请求对象
             * @return mixed 返回JSON格式的响应结果
             */
            [$file, $uploadToken, $pid] = $request->postMore([
                ['file', 'file'], // 获取上传的文件
                ['uploadToken', ''], // 获取上传令牌
                ['pid', 0] // 获取项目ID
                    ], true);
            $service = app()->make(SystemAttachmentServices::class); // 实例化系统附件服务类
            if (CacheService::get('scan_upload') != $uploadToken) { // 判断上传令牌是否正确
                return app('json')->fail(410086); // 上传令牌错误，返回失败响应
                    }
            $service->upload((int)$pid, $file, $upload_type, $type, '', $uploadToken); // 调用系统附件服务类的上传方法
            return app('json')->success(100032); // 返回成功响应

    }

    public function import(Request $request)
    {
        $filePath = $request->param('file_path', ''); // 从请求参数中获取文件路径
        if (empty($filePath)) { // 如果文件路径为空
            return app('json')->fail(12894); // 返回失败响应，错误码为12894
                }
        app()->make(SystemRouteServices::class)->import($filePath); // 调用SystemRouteServices类的import方法导入系统路由
        return app('json')->success(100010); // 返回成功响应，状态码为100010

    }
}
