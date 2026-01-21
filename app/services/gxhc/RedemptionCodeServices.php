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
use app\services\user\UserServices;
use app\dao\gxhc\RedemptionCodeDao;
use app\model\gxhc\RedemptionCode;
use crmeb\exceptions\AdminException;
use crmeb\exceptions\ApiException;
use think\facade\Db;
use think\facade\Log;

/**
 * 兑换码服务层
 * Class RedemptionCodeServices
 * @package app\services\gxhc
 * @method getList(array $where, string $field, int $page, int $limit, string $order) 获取列表
 * @method count(array $where) 获取数量
 * @method save(array $data) 保存
 * @method update($id, array $data) 更新
 * @method delete($id) 删除
 */
class RedemptionCodeServices extends BaseServices
{
    /**
     * RedemptionCodeServices constructor.
     * @param RedemptionCodeDao $dao
     */
    public function __construct(RedemptionCodeDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 生成随机码部分
     * @param int $length
     * @return string
     */
    protected function generateRandomCode($uid, int $length = 16): string
    {
        $uidStr = (string)$uid;
        $uidLength = strlen($uidStr);

        // 如果uid长度超过指定长度，直接返回uid截断版本
        if ($uidLength >= $length) {
            return substr($uidStr, 0, $length);
        }

        // 在第8位开始插入uid（如果长度足够）
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $code = '';

        // 生成前半部分随机码
        $prefixLength = min(8, $length - $uidLength);
        for ($i = 0; $i < $prefixLength; $i++) {
            $code .= $characters[random_int(0, strlen($characters) - 1)];
        }

        // 插入uid
        $code .= $uidStr;

        // 生成剩余的随机码
        $remainingLength = $length - strlen($code);
        for ($i = 0; $i < $remainingLength; $i++) {
            $code .= $characters[random_int(0, strlen($characters) - 1)];
        }

        return $code;
    }

    /**
     * 检查兑换码是否存在
     * @param string $code
     * @return bool
     */
    public function codeExists(string $code): bool
    {
        return $this->dao->codeExists($code);
    }

    /**
     * 生成完整兑换码（带渠道追踪）
     * @param string $channelCode 渠道代码
     * @param string $agentId 代理ID
     * @return string
     */
    protected function generateFullCode(string $channelCode = '', string $agentId = ''): string
    {
        $randomPart = $this->generateRandomCode(16);

        // 如果有渠道和代理信息，添加追踪后缀
        if ($channelCode && $agentId) {
            return $randomPart . '-' . $channelCode . '-' . $agentId;
        }

        return $randomPart;
    }

    /**
     * 解析兑换码获取渠道信息
     * @param string $code
     * @return array ['random_part' => '', 'channel_code' => '', 'agent_id' => '']
     */
    public function parseCode(string $code): array
    {
        $parts = explode('-', $code);

        if (count($parts) >= 3) {
            // 格式: 随机码-渠道代码-代理ID
            return [
                'random_part' => $parts[0],
                'channel_code' => $parts[1],
                'agent_id' => $parts[2]
            ];
        }

        return [
            'random_part' => $code,
            'channel_code' => '',
            'agent_id' => ''
        ];
    }

    /**
     * 系统批量生成兑换码（后台管理）
     * @param array $data
     * @return array
     * @throws AdminException
     */
    public function systemGenerateCodes(array $data): array
    {
        $quantity = (int)($data['quantity'] ?? 1);
        $channelCode = trim($data['channel_code'] ?? '');
        $agentId = trim($data['agent_id'] ?? '');
        $energyValue = (int)($data['energy_value'] ?? RedemptionCode::DEFAULT_ENERGY_VALUE);
        $expireDays = (int)($data['expire_days'] ?? 0); // 0表示永不过期
        $remark = trim($data['remark'] ?? '');

        if ($quantity < 1 || $quantity > 1000) {
            throw new AdminException('生成数量必须在1-1000之间');
        }

        if ($energyValue < 1) {
            throw new AdminException('能量值必须大于0');
        }

        if ($channelCode && !$agentId) {
            throw new AdminException('渠道代码和代理ID必须同时填写');
        }

        $codes = [];
        $expireTime = $expireDays > 0 ? time() + ($expireDays * 86400) : 0;

        Db::startTrans();
        try {
            for ($i = 0; $i < $quantity; $i++) {
                // 生成唯一的兑换码
                $maxAttempts = 10;
                $code = '';
                for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
                    $code = $this->generateFullCode($channelCode, $agentId);
                    if (!$this->dao->codeExists($code)) {
                        break;
                    }
                    if ($attempt === $maxAttempts - 1) {
                        throw new AdminException('生成唯一兑换码失败，请重试');
                    }
                }

                $codeData = [
                    'code' => $code,
                    'energy_value' => $energyValue,
                    'status' => RedemptionCode::STATUS_UNUSED,
                    'source_type' => RedemptionCode::SOURCE_SYSTEM,
                    'creator_uid' => 0, // 系统生成，无用户ID
                    'channel_code' => $channelCode,
                    'agent_id' => $agentId,
                    'expire_time' => $expireTime,
                    'remark' => $remark,
                    'create_time' => time(),
                    'update_time' => time()
                ];

                $this->dao->save($codeData);
                $codes[] = $code;
            }
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            Log::error('系统生成兑换码失败: ' . $e->getMessage());
            throw new AdminException('生成兑换码失败: ' . $e->getMessage());
        }

        return [
            'count' => count($codes),
            'codes' => $codes,
            'channel_code' => $channelCode,
            'agent_id' => $agentId,
            'energy_value' => $energyValue
        ];
    }

    /**
     * 用户铸造兑换码（前端用户）
     * @param int $uid
     * @param int $energyValue
     * @return array
     * @throws ApiException
     */
    public function userMintCode(int $uid, int $energyValue = 0): array
    {
        if ($energyValue <= 0) {
            $energyValue = RedemptionCode::DEFAULT_ENERGY_VALUE;
        }

        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $userInfo = $userServices->getUserInfo($uid);

        if (!$userInfo) {
            throw new ApiException('用户不存在');
        }

        $currentEnergy = (int)$userInfo['energy'];
        if ($currentEnergy < $energyValue) {
            throw new ApiException('能量不足，当前能量: ' . $currentEnergy . '，需要: ' . $energyValue);
        }

        $count = $this->dao->getUserTodayMintedCount($uid);
        if ($count >= RedemptionCode::MAX_MINT_COUNT_PER_DAY) {
            throw new ApiException('今日生成兑换码数量已达到上限');
        }

        Db::startTrans();
        try {
            // 生成唯一兑换码（用户铸造的码不带渠道追踪）
            $maxAttempts = 10;
            $code = '';
            for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
                $code = $this->generateRandomCode($uid, 20);
                if (!$this->dao->codeExists($code)) {
                    break;
                }
                if ($attempt === $maxAttempts - 1) {
                    throw new ApiException('生成兑换码失败，请重试');
                }
            }

            // 扣除用户能量
            $res = $userServices->bcDec($uid, 'energy', (string)$energyValue, 'uid');
            if (!$res) {
                throw new ApiException('扣除能量失败');
            }

            // 记录能量流水
            /** @var UserEnergyServices $userEnergyServices */
            $userEnergyServices = app()->make(UserEnergyServices::class);
            $newBalance = $currentEnergy - $energyValue;
            $userEnergyServices->expendIntegral($uid, 'mint_redemption_code', [
                'number' => $energyValue,
                'balance' => $newBalance,
                'link_id' => 0,
                'title' => '生成兑换码',
                'mark' => '生成兑换码扣除' . $energyValue . '能量'
            ]);

            // 保存兑换码
            $codeData = [
                'code' => $code,
                'energy_value' => $energyValue,
                'status' => RedemptionCode::STATUS_UNUSED,
                'source_type' => RedemptionCode::SOURCE_USER,
                'creator_uid' => $uid,
                'channel_code' => '',
                'agent_id' => '',
                'expire_time' => 0, // 用户铸造的码默认永不过期
                'remark' => '生成兑换码',
                'create_time' => time(),
                'update_time' => time()
            ];

            $this->dao->save($codeData);
            Db::commit();

            return [
                'code' => $code,
                'energy_value' => $energyValue,
                'new_balance' => $newBalance
            ];
        } catch (\Exception $e) {
            Db::rollback();
            Log::error('用户生成兑换码失败: uid=' . $uid . ', error=' . $e->getMessage());
            if ($e instanceof ApiException) {
                throw $e;
            }
            throw new ApiException('生成兑换码失败，请重试');
        }
    }

    /**
     * 核销兑换码
     * @param int $uid
     * @param string $code
     * @return array
     * @throws ApiException
     */
    public function redeemCode(int $uid, string $code): array
    {
        $code = trim($code);
        if (empty($code)) {
            throw new ApiException('请输入兑换码');
        }

        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $userInfo = $userServices->getUserInfo($uid);

        if (!$userInfo) {
            throw new ApiException('用户不存在');
        }

        // 查找兑换码
        $redemptionCode = $this->dao->getByCode($code);
        if (!$redemptionCode) {
            throw new ApiException('兑换码不存在');
        }

        // 检查状态
        if ($redemptionCode['status'] == RedemptionCode::STATUS_USED) {
            throw new ApiException('兑换码已被使用');
        }

        // if ($redemptionCode['status'] == RedemptionCode::STATUS_EXPIRED) {
        //     throw new ApiException('兑换码已过期');
        // }

        if ($redemptionCode['status'] == RedemptionCode::STATUS_DISABLED) {
            throw new ApiException('兑换码已被禁用');
        }

        // 检查是否过期（实时检查）
        // if ($redemptionCode['expire_time'] > 0 && $redemptionCode['expire_time'] < time()) {
        //     // 更新状态为过期
        //     $this->dao->updateStatus($redemptionCode['id'], [
        //         'status' => RedemptionCode::STATUS_EXPIRED,
        //         'update_time' => time()
        //     ]);
        //     throw new ApiException('兑换码已过期');
        // }

        // 检查是否是自己铸造的码（不能自己兑换自己铸造的码）
        // if (
        //     $redemptionCode['source_type'] == RedemptionCode::SOURCE_USER &&
        //     $redemptionCode['creator_uid'] == $uid
        // ) {
        //     throw new ApiException('不能兑换自己铸造的兑换码');
        // }

        $energyValue = (int)$redemptionCode['energy_value'];
        $currentEnergy = (int)$userInfo['energy'];

        Db::startTrans();
        try {
            // 增加用户能量
            $res = $userServices->bcInc($uid, 'energy', (string)$energyValue, 'uid');
            if (!$res) {
                throw new ApiException('增加能量失败');
            }

            // 记录能量流水
            /** @var UserEnergyServices $userEnergyServices */
            $userEnergyServices = app()->make(UserEnergyServices::class);
            $newBalance = $currentEnergy + $energyValue;
            $userEnergyServices->incomeIntegral($uid, 'redeem_code', [
                'number' => $energyValue,
                'balance' => $newBalance,
                'link_id' => $redemptionCode['id'],
                'title' => '兑换码核销',
                'mark' => '兑换码核销获得' . $energyValue . '能量'
            ]);

            // 更新兑换码状态
            $this->dao->updateStatus($redemptionCode['id'], [
                'status' => RedemptionCode::STATUS_USED,
                'used_by_uid' => $uid,
                'used_time' => time(),
                'update_time' => time()
            ]);

            Db::commit();

            return [
                'energy_value' => $energyValue,
                'new_balance' => $newBalance,
                'channel_code' => $redemptionCode['channel_code'],
                'agent_id' => $redemptionCode['agent_id']
            ];
        } catch (\Exception $e) {
            Db::rollback();
            Log::error('核销兑换码失败: uid=' . $uid . ', code=' . $code . ', error=' . $e->getMessage());
            if ($e instanceof ApiException) {
                throw $e;
            }
            throw new ApiException('核销兑换码失败，请重试');
        }
    }

    /**
     * 获取用户铸造的兑换码列表
     * @param int $uid
     * @return array
     */
    public function getUserMintedCodes(int $uid, int $page = 1, int $limit = 10): array
    {
        $list = $this->dao->getUserMintedCodes($uid, $page, $limit);
        $count = $this->dao->getUserMintedCount($uid);

        // 处理状态显示
        foreach ($list as &$item) {
            $item['status_text'] = $this->getStatusText($item['status']);
        }

        return compact('list', 'count');
    }

    /**
     * 获取用户兑换码列表
     * @param int $uid
     * @return array
     */
    public function getUserUseCode(int $uid, int $page = 1, int $limit = 10): array
    {
        $list = $this->dao->getUserUseCodes($uid, $page, $limit);
        $count = $this->dao->getUserUseCount($uid);

        // 处理状态显示
        foreach ($list as &$item) {
            $item['status_text'] = $this->getStatusText($item['status']);
        }

        return compact('list', 'count');
    }

    /**
     * 获取状态文本
     * @param int $status
     * @return string
     */
    protected function getStatusText(int $status): string
    {
        $statusMap = [
            RedemptionCode::STATUS_UNUSED => '未使用',
            RedemptionCode::STATUS_USED => '已使用',
            RedemptionCode::STATUS_EXPIRED => '已过期',
            RedemptionCode::STATUS_DISABLED => '已禁用'
        ];
        return $statusMap[$status] ?? '未知';
    }

    /**
     * 获取兑换码详情
     * @param string $code
     * @return array
     */
    public function getCodeDetail(string $code): array
    {
        $redemptionCode = $this->dao->getByCode($code);
        if (!$redemptionCode) {
            throw new ApiException('兑换码不存在');
        }

        $detail = $redemptionCode->toArray();
        $detail['status_text'] = $this->getStatusText($detail['status']);
        $detail['source_type_text'] = $detail['source_type'] == RedemptionCode::SOURCE_SYSTEM ? '系统生成' : '用户铸造';

        return $detail;
    }

    /**
     * 获取兑换码列表（管理后台）
     * @param array $where
     * @return array
     */
    public function getAdminList(array $where): array
    {
        $page = (int)($where['page'] ?? 1);
        $limit = (int)($where['limit'] ?? 10);
        $list = $this->dao->getListWithUser($where, '*', $page, $limit);
        $count = $this->dao->count($where);

        foreach ($list as &$item) {
            $item['status_text'] = $this->getStatusText($item['status']);
            $item['source_type_text'] = $item['source_type'] == RedemptionCode::SOURCE_SYSTEM ? '系统生成' : '用户铸造';
            $item['creator_nickname'] = $item['creator']['nickname'] ?? '-';
            $item['used_user_nickname'] = $item['usedUser']['nickname'] ?? '-';
            unset($item['creator'], $item['usedUser']);
        }

        return compact('list', 'count');
    }

    /**
     * 获取渠道统计数据
     * @param array $where
     * @return array
     */
    public function getChannelStatistics(array $where = []): array
    {
        $statistics = $this->dao->getChannelStatistics($where);
        $summary = $this->dao->getChannelSummary($where);

        return [
            'detail' => $statistics,
            'summary' => $summary
        ];
    }

    /**
     * 获取渠道代理报表
     * @param array $where
     * @return array
     */
    public function getChannelAgentReport(array $where = []): array
    {
        $channelCode = $where['channel_code'] ?? '';

        $searchWhere = [];
        if ($channelCode) {
            $searchWhere['channel_code'] = $channelCode;
        }
        $searchWhere['source_type'] = RedemptionCode::SOURCE_SYSTEM;

        $statistics = $this->dao->getChannelStatistics($searchWhere);

        // 按渠道分组
        $report = [];
        foreach ($statistics as $item) {
            $channel = $item['channel_code'] ?: '无渠道';
            if (!isset($report[$channel])) {
                $report[$channel] = [
                    'channel_code' => $item['channel_code'],
                    'channel_name' => $this->getChannelName($item['channel_code']),
                    'agents' => [],
                    'total_count' => 0,
                    'used_count' => 0,
                    'unused_count' => 0,
                    'used_energy' => 0,
                    'conversion_rate' => 0
                ];
            }

            $report[$channel]['agents'][] = [
                'agent_id' => $item['agent_id'],
                'total_count' => $item['total_count'],
                'used_count' => $item['used_count'],
                'unused_count' => $item['unused_count'],
                'used_energy' => $item['used_energy'],
                'conversion_rate' => $item['total_count'] > 0 ?
                    round($item['used_count'] / $item['total_count'] * 100, 2) : 0
            ];

            $report[$channel]['total_count'] += $item['total_count'];
            $report[$channel]['used_count'] += $item['used_count'];
            $report[$channel]['unused_count'] += $item['unused_count'];
            $report[$channel]['used_energy'] += $item['used_energy'];
        }

        // 计算各渠道转化率
        foreach ($report as &$channelData) {
            $channelData['conversion_rate'] = $channelData['total_count'] > 0 ?
                round($channelData['used_count'] / $channelData['total_count'] * 100, 2) : 0;
        }

        return array_values($report);
    }

    /**
     * 获取渠道名称
     * @param string $channelCode
     * @return string
     */
    protected function getChannelName(string $channelCode): string
    {
        $channelMap = [
            'ZB' => '直播渠道',
            '2B' => '园区直销',
            'DL' => '代理商',
            'YX' => '营销活动',
            'KH' => '客户推荐'
        ];
        return $channelMap[$channelCode] ?? $channelCode;
    }

    /**
     * 禁用/启用兑换码
     * @param int $id
     * @param int $status
     * @return bool
     */
    public function setCodeStatus($id, $status)
    {
        $code = $this->dao->get($id);
        if (!$code) {
            throw new AdminException('兑换码不存在');
        }

        if ($code['status'] == RedemptionCode::STATUS_USED) {
            throw new AdminException('已使用的兑换码不能修改状态');
        }

        return $this->dao->updateStatus($id, [
            'status' => $status,
            'update_time' => time()
        ]);
    }

    /**
     * 删除兑换码
     */
    public function deleteCode($id)
    {
        $code = $this->dao->get($id);
        if (!$code) {
            throw new AdminException('兑换码不存在');
        }

        if ($code['status'] == RedemptionCode::STATUS_USED) {
            throw new AdminException('已使用的兑换码不能删除');
        }

        return $this->dao->delete($id);
    }

    /**
     * 处理过期兑换码（定时任务调用）
     * @return int 处理数量
     */
    public function processExpiredCodes(): int
    {
        $expiredIds = $this->dao->getExpiredCodeIds();
        if (empty($expiredIds)) {
            return 0;
        }

        $this->dao->batchSetExpired($expiredIds);
        return count($expiredIds);
    }

    /**
     * 获取统计概览数据
     * @return array
     */
    public function getOverviewStatistics(): array
    {
        $totalCount = $this->dao->count([]);
        $usedCount = $this->dao->count(['status' => RedemptionCode::STATUS_USED]);
        $unusedCount = $this->dao->count(['status' => RedemptionCode::STATUS_UNUSED]);
        $expiredCount = $this->dao->count(['status' => RedemptionCode::STATUS_EXPIRED]);
        $systemCount = $this->dao->count(['source_type' => RedemptionCode::SOURCE_SYSTEM]);
        $userCount = $this->dao->count(['source_type' => RedemptionCode::SOURCE_USER]);

        return [
            'total_count' => $totalCount,
            'used_count' => $usedCount,
            'unused_count' => $unusedCount,
            'expired_count' => $expiredCount,
            'system_count' => $systemCount,
            'user_count' => $userCount,
            'conversion_rate' => $totalCount > 0 ? round($usedCount / $totalCount * 100, 2) : 0
        ];
    }

    /**
     * 管理员创建单个兑换码
     * @param array $data
     * @return array
     * @throws AdminException
     */
    public function adminCreateCode(array $data): array
    {
        // 验证必填项
        if (empty($data['energy_value']) || $data['energy_value'] <= 0) {
            throw new AdminException('能量值必须大于0');
        }

        // 处理过期时间
        $expireTime = 0; // 默认永不过期
        if (!empty($data['expire_time'])) {
            $expireTime = strtotime($data['expire_time']);
            if ($expireTime === false) {
                throw new AdminException('过期时间格式错误');
            }
        }

        Db::startTrans();
        try {
            // 生成唯一兑换码
            $maxAttempts = 10;
            $code = '';
            for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
                $code = $this->generateRandomCode(0, 16); // 使用0作为uid，生成16位随机码
                if (!$this->dao->codeExists($code)) {
                    break;
                }
                if ($attempt === $maxAttempts - 1) {
                    throw new AdminException('生成唯一兑换码失败，请重试');
                }
            }

            // 准备数据
            $codeData = [
                'code' => $code,
                'energy_value' => (int)$data['energy_value'],
                'status' => RedemptionCode::STATUS_UNUSED,
                'source_type' => RedemptionCode::SOURCE_SYSTEM,
                'creator_uid' => 0, // 系统生成，无用户ID
                'channel_code' => trim($data['channel_code'] ?? ''),
                'agent_id' => trim($data['agent_id'] ?? ''),
                'expire_time' => $expireTime,
                'remark' => trim($data['remark'] ?? ''),
                'create_time' => time(),
                'update_time' => time()
            ];

            // 保存到数据库
            $result = $this->dao->save($codeData);

            if ($result) {
                Db::commit();
                return [
                    'id' => $result->id,
                    'code' => $code,
                    'message' => '新增兑换码成功'
                ];
            } else {
                throw new AdminException('新增兑换码失败');
            }
        } catch (\Exception $e) {
            Db::rollback();
            Log::error('新增兑换码失败: ' . $e->getMessage());
            throw new AdminException($e->getMessage());
        }
    }
}
