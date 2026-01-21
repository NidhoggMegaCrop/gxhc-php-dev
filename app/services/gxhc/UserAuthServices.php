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
use app\dao\gxhc\UserAuthDao;
use crmeb\exceptions\AdminException;
use crmeb\services\FormBuilder as Form;
use think\facade\Route as Url;


/**
 *
 * Class UserAuthServices
 * @package app\services\UserAuth
 */
class UserAuthServices extends BaseServices
{

    /**
     * UserAuthServices constructor.
     * @param UserAuthrDao $dao
     */
    public function __construct(UserAuthDao $dao)
    {
        $this->dao = $dao;
    }

    public function auth($data)
    {
        $uid = $data['uid'] ?? 0;
        if (!$uid) {
            throw new AdminException('用户ID不能为空');
        }

        // 根据uid查询是否已存在认证信息
        $userInfo = $this->dao->getOne(['uid' => $uid]);

        // 准备要保存的数据
        $saveData = [
            'identity_type' => $data['identity_type'],
            'uid'           => $data['uid'],
        ];

        if ($data['identity_type'] == 'finance') {
            $saveData['institution'] = $data['institution'] ?? '';
            $saveData['institution_type'] = $data['institution_type'] ?? '';
            $saveData['business_card'] = $data['business_card'] ?? '';
        } else {
            $saveData['company_name'] = $data['company_name'] ?? '';
            $saveData['business_position'] = $data['business_position'] ?? '';
            $saveData['industry'] = is_array($data['industry']) ? json_encode($data['industry'], JSON_UNESCAPED_UNICODE) : ($data['industry'] ?? '');
            $saveData['business_card2'] = $data['business_card2'] ?? '';
        }

        // 如果已存在记录，则更新
        if ($userInfo) {
            $saveData['last_time'] = time();
            $result = $this->dao->update($userInfo['id'], $saveData);
        } else {
            // 如果不存在记录，则新增
            $saveData['add_time'] = time();
            $result = $this->dao->save($saveData);
        }

        if (!$result) {
            throw new AdminException('操作失败');
        } else {
            return true;
        }
    }
}
