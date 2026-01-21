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

use app\services\gxhc\InvestProjectsServices;
use crmeb\services\CacheService;
use app\Request;

class InvestProjectsController
{
    protected $services = NUll;

    /**
     * InvestProjectsController constructor.
     * @param InvestProjectsServices $services
     */
    public function __construct(InvestProjectsServices $services)
    {
        $this->services = $services;
    }

    public function update_score(Request $request)
    {
        $postData = $request->postMore([
            ["id", 0],
            ["totalScore", 0],
            ["scoreState", ''],
        ]);
        if (!$postData['id']) {
            return app('json')->fail('id error');
        }
        $res = $this->services->editProject($postData);
        if ($res) {
            return app('json')->success($res['type'] == 'edit' ? 100001 : $res['data']);
        } else {
            return app('json')->fail(100007);
        }
    }

    public function update_supply(Request $request)
    {
        $postData = $request->postMore([
            ["id", 0],
            ["latestBusines", ''],
            ["finacialReport", ''],
            ["councilSuport2", ''],
        ]);
        if (!$postData['id']) {
            return app('json')->fail('id error');
        }
        $res = $this->services->editProject($postData);
        if ($res) {
            return app('json')->success($res['data']);
        } else {
            return app('json')->fail(100007);
        }
    }

    public function save(Request $request)
    {
        $postData = $request->postMore([
            ["id", 0],
            ["companyName", ''],
            ["companyBrief", ''],
            ["setupDate", ''],
            ["projectBrief", ''],
            ["industry", ''],
            ["businessModel", ''],
            ["productIntro", ''],
            ["coreCompete", ''],
            ["marketPain", ''],
            ["marketSize", ''],
            ["sizeMeasurement", ''],
            ["businessPlan", ''],
            ["productPlan", ''],
            ["financePlan", ''],
            ["teamDevPlan", ''],
            ["isExternalInvest", ''],
            ["egInvestBrief", ''],
            ["valuation", ''],
            ["isFounderCtrl", ''],
            ["isTechCoreTeam", ''],
            ["founderStockRat", ''],
            ["isTeamInvested", ''],
            ["equityStruct", ''],
            ["other", ''],
            ["isCouncilMember", ''],
            ["councilSupport", ''],
            ["name", ''],
            ["mobile", ''],
            ["email", ''],
            ["referrer", ''],
            ["referrerMobile", ''],
            ["uploadBPPath", ''],
            ["projectStatus", ''],
            ["latestBusines", ''],
            ["finacialReport", ''],
            ["bizPlan", ''],
            ["councilSuport2", ''],
            ["teamList", []],
        ]);
        // $postData2 = $request->post();
        // var_dump($postData, $postData2);
        $uid = (int)$request->uid();
        $postData['uid'] = $uid;
        $res = $this->services->saveInvesProjects($uid, $postData);
        if ($res) {
            return app('json')->success($res['type'] == 'edit' ? 100001 : $res['data']);
        } else {
            return app('json')->fail(100007);
        }
    }

    public function details(Request $request)
    {
        $postData = $request->postMore([
            ["id", 0],
        ]);
        $postData['uid'] = (int)$request->uid();
        return app('json')->success($this->services->details($postData));
    }

    public function revoke(Request $request)
    {
        $postData = $request->postMore([
            ["id", 0],
            ["is_del", 1],
        ]);
        return app('json')->success($this->services->editProject($postData));
    }

    public function update_bp(Request $request)
    {
        $postData = $request->postMore([
            ["id", 0],
            ["uploadBPPath", ''],
        ]);
         if (!$postData['id']) {
            return app('json')->fail('id error');
        }
        if (!$postData['uploadBPPath']) {
            return app('json')->fail('请选择文件');
        }
        return app('json')->success($this->services->editProject($postData));
    }
}
