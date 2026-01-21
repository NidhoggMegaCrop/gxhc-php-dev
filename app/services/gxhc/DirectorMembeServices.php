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
declare(strict_types=1);

namespace app\services\gxhc;

use app\services\BaseServices;
use app\dao\gxhc\DirectorMemberDao;
use crmeb\exceptions\AdminException;
use crmeb\services\FormBuilder as Form;
use think\facade\Route as Url;


/**
 *
 * Class DirectorMembeServices
 * @package app\services\directorMembe
 */
class DirectorMembeServices extends BaseServices
{

    /**
     * DirectorMembeServices constructor.
     * @param DirectorMemberDao $dao
     */
    public function __construct(DirectorMemberDao $dao)
    {
        $this->dao = $dao;
    }

    public function handPhoto($photo)
    {
        $isHttp = preg_match('/^https?:\/\//i', $photo);
        $photo = $isHttp ? $photo : sys_config('site_url') . '/statics' . $photo;
        return $photo;
    }

    public function newList()
    {
        $list = $this->dao->selectList([], '*', 1, 3, '', [], true)->toArray();
        foreach ($list as &$item) {
            $item['photo'] = $this->handPhoto($item['photo']);
        }
        return $list;
    }

    public function list()
    {
        $list = $this->dao->list()->toArray();
        $result = [];
        foreach ($list as &$item) {
            $item['photo'] = $this->handPhoto($item['photo']);
            $result[$item['directorType']][] = $item;
        }
        return $result;
    }

    public function details($id)
    {
        $info = $this->dao->get($id)->toArray();
        $info['photo'] = $item['photo'] = $this->handPhoto($info['photo']);
        return $info;
    }

    /**
     * 获取页面链接
     * @param array $where
     * @return array
     */
    public function getLinkList(array $where)
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getList($where, '*', $page, $limit);
        $count = $this->dao->count($where);
        foreach ($list as &$item) {
            $item['h5_url'] = sys_config('site_url') . $item['url'];
            $item['add_time'] = date('Y-m-d H:i:s', $item['add_time']);
        }
        return compact('list', 'count');
    }

    /**
     * 删除
     * @param int $id
     */
    public function del(int $id)
    {
        $res = $this->dao->delete($id);
        if (!$res) throw new AdminException(100008);
    }

    public function getLinkSave($id, $data)
    {
        unset($data['id']);
        if ($id) {
            $res = $this->dao->update($id, $data);
        } else {
            $data['add_time'] = time();
            $data['status'] = 1;
            $res = $this->dao->save($data);
        }
        if (!$res) {
            throw new AdminException('保存失败');
        } else {
            return true;
        }
    }
}
