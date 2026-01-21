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
use app\dao\gxhc\InvestProjectsDao;
use crmeb\exceptions\AdminException;
use crmeb\services\FormBuilder as Form;
use crmeb\exceptions\ApiException;


/**
 *
 * Class InvestProjectsServices
 * @package app\services\investProjects
 */
class InvestProjectsServices extends BaseServices
{

    /**
     * InvestProjectsServices constructor.
     * @param InvestProjectsrDao $dao
     */
    public function __construct(InvestProjectsDao $dao)
    {
        $this->dao = $dao;
    }

    public function saveInvesProjects($uid, $data)
    {
        $data['teamList'] = !empty($data['teamList']) ? json_encode($data['teamList']) : '';
        if ($data['id']) {
            $data['update_time'] = time();
            if (!$this->dao->update($data['id'], $data, 'id')) {
                throw new ApiException(100007);
            }
            return ['type' => 'edit', 'msg' => '编辑成功', 'data' => []];
        } else {
            if ($this->dao->be(['uid' => $uid, 'is_del' => 0])) {
                throw new ApiException('请勿重复申请');
            }
            $data['add_time'] = time();
            if (!$project = $this->dao->save($data)) {
                throw new ApiException(100022);
            }
            return ['type' => 'add', 'msg' => '提交成功', 'data' => ['id' => $project->id]];
        }
    }

    public function editProject($data)
    {
        $data['update_time'] = time();
        if ($data['id']) {
            if (!$this->dao->update($data['id'], $data, 'id')) {
                throw new ApiException(100007);
            }
            return ['msg' => '操作成功', 'data' => []];
        }
        throw new ApiException(100022);
    }

    public function details($data)
    {
        $where = ['is_del' => 0];
        if (!empty($data['id'])) {
            $where['id'] = $data['id'];
        } else {
            $where['uid'] = $data['uid'];
        }
        $info = $this->dao->new_details($where);
        return $info ? $info->toArray() : [];
    }
}
