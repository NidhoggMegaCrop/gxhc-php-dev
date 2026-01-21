<?php

namespace app\services\system\log;

use app\dao\system\log\SystemFileInfoDao;
use app\services\BaseServices;

/**
 * @author 吴汐
 * @email 442384644@qq.com
 * @date 2023/04/07
 */
class SystemFileInfoServices extends BaseServices
{
    // 排除部分目录
    protected $excluded_directories = array(
        '/runtime/cache',
        '/runtime/log',
        '/runtime/session',
        '/runtime/temp',
        '/public/uploads/attach',
        '/public/install/images/install',
        '/public/admin/system_static/css',
        '/public/admin/system_static/js',
        '/public/admin/system_static/img',
        '/public/admin/system_static/fonts',
        '/public/admin/system_static/media',
        '/public/static/css',
        '/public/static/js',
        '/public/static/img',
        '/public/static/images',
        '/public/statics/images',
        '/public/statics/mp_view/static',
        '/vendor'
        );
    /**
     * 构造方法
     * SystemLogServices constructor.
     * @param SystemFileInfoDao $dao
     */
    public function __construct(SystemFileInfoDao $dao)
    {
        $this->dao = $dao;
    }
    //命令执行保存所有文件目录
    public function syncfile()
    {
        // 展平目录扫描结果数组
        $list = $this->flattenArray($this->scanDirectory());
        $this->dao->saveAll($list);
    }
    //数据库中不存在的新增保存目录或文件信息
    public function openSave($value,$list_key)   
    {
        $count = $this->count(['full_path' => $list_key['real_path']]);
        if(!$count) {
            $path = str_replace('/' . $value['filename'], '', $list_key['real_path']);
            if(!$this->is_array_contain_string($path)){
                $this->save([
                    'name' => $value['filename'],
                    'path' => str_replace('/' . $value['filename'], '', $list_key['real_path']),
                    'full_path' => $list_key['real_path'],
                    'type' => $value['type'],
                    'create_time' => date('Y-m-d H:i:s', $value['ctime']),
                    'update_time' => date('Y-m-d H:i:s', time()),
                ]);
            }
        }
        
    }
    //查询字符串出现过数组中的字符，出现返回true，否则返回false
    public function is_array_contain_string($string) {
        foreach ($this->excluded_directories as $item) {
            if (strpos($string,$item) === 0) {
                return true;
            }
        }
        return false;
    }
    //获取目录中所有文件和子目录
    public function scanDirectory($dir = '')
    {
        if ($dir == '') $dir = root_path();
        $result = array();
        // 获取目录下的所有文件和子目录
        $files = array_diff(scandir($dir), array('.', '..'));
        // 遍历文件和子目录
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            $fileInfo = array(
                'name' => $file,
                'update_time' => date('Y-m-d H:i:s', filemtime($path)),
                'create_time' => date('Y-m-d H:i:s', filectime($path)),
                'path' => str_replace(root_path(), '', $dir),
                'full_path' => str_replace(root_path(), '', $path),
            );
            // 判断是否是目录
            if (is_dir($path) && !in_array($file, $this->excluded_directories)) {
                $fileInfo['type'] = 'dir';
                $fileInfo['contents'] = $this->scanDirectory($path);
            } else {
                $fileInfo['type'] = 'file';
            }
            $result[] = $fileInfo;
        }
        return $result;
    }
    
    public function flattenArray($arr)
    {
        $result = array();
        foreach ($arr as $item) {
            if(!$this->is_array_contain_string($item['path'])){
                $result[] = array(
                    'name' => $item['name'],
                    'type' => $item['type'],
                    'update_time' => $item['update_time'],
                    'create_time' => $item['create_time'],
                    'path' => $item['path'],
                    'full_path' => $item['full_path'],
                );
                if (isset($item['contents'])) {
                    $result = array_merge($result, $this->flattenArray($item['contents']));
                }
            }
        }
        return $result;
    }
}