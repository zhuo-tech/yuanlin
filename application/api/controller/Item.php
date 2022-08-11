<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\service\FactorService;
use app\common\service\ItemFactorService;
use app\common\service\ItemService;
use think\Request;

/**
 * 项目接口
 */
class Item extends Api {
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * @brief  创建项目
     * @ApiParams   (name="name", type="string", required=true, description="项目名称")
     * @ApiParams   (name="item_type", type="int", required=true, description="项目类型")
     * @ApiParams   (name="province", type="int", required=true, description="省CODE")
     * @ApiParams   (name="city", type="int", required=true, description="市code")
     * @ApiParams   (name="area", type="int", required=true, description="区code")
     * @ApiParams   (name="areas", type="float", required=true, description="面积")
     * @ApiParams   (name="item_cate_id", type="int", required=true, description="类型")
     * @ApiParams   (name="images", type="string", required=true, description="图片")
     * @ApiParams   (name="introduction", type="string", required=true, description="项目描述")
     * @ApiParams   (name="build_time", type="string", required=false, description="落成时间")
     * @ApiParams   (name="designer_team", type="string", required=true, description="设计团队")
     * @ApiParams   (name="study_team", type="string", required=false, description="研究团队")
     * @ApiReturn   ({
    'code':'0',
    'mesg':'返回成功'
    })
     *
     */
    public function store(Request $request) {
        $data = ItemService::saveItem($request->param());
        return json(['code' => $data['error'], 'data' => [], 'message' => $data['message']]);
    }

    /**
     * @brief 根据分类查询
     *
     * @ApiParams   (name="page", type="string", required=false, description="页数")
     * @ApiParams   (name="cid", type="string", required=false, description="分类ID")
     * @ApiParams   (name="keyword", type="string", required=false, description="搜索关键词")
     * @ApiReturn   ({
    'code':'0',
    'mesg':'返回成功',
    })
     */
    public function query(Request $request) {
        $page    = $request->param('page', 0);
        $bid     = $request->param('bid', 0);
        $keyword = $request->param('keyword', '');

        $data = ItemService::cate(['cid' => $cid, 'keyword' => $keyword], $page);
        return json(['code' => 0, 'message' => 'OK', 'data' => $data]);
    }

    public function
}
