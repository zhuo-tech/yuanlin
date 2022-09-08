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
     * @brief 创建项目
     */
    public function store(Request $request) {
        $data = ItemService::saveItem($request->param());
        return json(['code' => $data['error'], 'data' => [], 'message' => $data['message']]);
    }

    /**
     * @brief 根据分类查询
     */
    public function query(Request $request) {
        $page    = $request->param('page', 0);
        $cid     = $request->param('cid', 0);
        $uid     = $request->param('uid', 0);
        $keyword = $request->param('keyword', '');

        $data = ItemService::cate(['cid' => $cid, 'user_id' => $uid, 'keyword' => $keyword], $page);
        return json(['code' => 0, 'message' => 'OK', 'data' => $data]);
    }

    /**
     * @brief 根据指标查询
     */
    public function search(Request $request) {
        $page    = $request->param('page', 0);
        $fid     = $request->param('fid', 0);
        $uid     = $request->param('uid', 0);
        $size    = $request->param('size', 10);
        $keyword = $request->param('keyword', '');

        $data = ItemService::search(['fid' => $fid, 'uid' => $uid, 'keyword' => $keyword], $page, (int)$size);
        return json(['code' => 0, 'message' => 'OK', 'data' => $data]);
    }

    /**
     * @brief 精选指标
     */
    public static function selected(Request $request) {
        $page = $request->param('page', 0);
        $data = FactorService::selected($page);
        return json(['code' => 0, 'message' => 'OK', 'data' => $data]);
    }
}
