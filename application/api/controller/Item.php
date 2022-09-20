<?php

namespace app\api\controller;

use app\admin\model\Factor as FactorModel;
use app\admin\model\Items as ItemsModel;
use app\admin\model\ItemsFactor;
use app\common\controller\Api;
use app\common\service\FactorService;
use app\common\service\ItemService;
use think\Request;

/**
 * 项目接口
 */
class Item extends Api {
    protected $noNeedLogin = ['query', 'search', 'selected', 'details', 'selectedTree'];
    protected $noNeedRight = ['*'];

    /**
     * @brief 创建项目
     */
    public function store(Request $request) {
        if (empty($this->auth->mobile)) {
            return json(['code' => 1, 'data' => [], 'message' => '请先绑定手机号再创建项目']);
        }
        $data = ItemService::saveItem($request->param(), $this->auth->id);
        return json(['code' => $data['error'], 'data' => $data['data'], 'message' => $data['message']]);
    }

    /**
     * @brief 根据分类查询
     */
    public function query(Request $request) {
        $page    = $request->param('page', 0);
        $cid     = $request->param('cid', 0);
        $uid     = $request->param('uid', 0);
        $keyword = $request->param('keyword', '');

        $data = ItemService::cate(['cid' => $cid, 'uid' => $uid, 'keyword' => $keyword], $page);
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
    public function selected(Request $request) {
        $page = $request->param('page', 0);
        $data = FactorService::selected($page);
        return json(['code' => 0, 'message' => 'OK', 'data' => $data]);
    }

    public function saveItem(Request $request){

        //var_dump(11);die;

        $itemId = $request->param('item_id', 0);

        $res = ItemsModel::update(['status' => 2], ['id' => $itemId]);

        return json_encode(['code' => 0, 'message' => 'OK', 'data' => []]);

    }

    public function details(Request $request){

        $itemId = $request->param('item_id', 0);

        $res = ItemsModel::get(['id' => $itemId]);

        if(!$res) json_encode(['code' =>1, 'message' => 'error', 'data' => []]);

        $res['location'] = implode('',explode('/',$res['location']));

        return json_encode(['code' => 0, 'message' => 'OK', 'data' => $res]);

    }

    public function selectedTree(Request $request) {

        $itemId = $request->param('item_id', 0);
        $first = FactorModel::where(['status' => 1, 'pid' => 0])->field("id,name")->select()->toArray();
        foreach ($first as &$v) {
            $v['children'] = $this->getChildren($v);
        }

        $selectRows    = ItemsFactor::where(['item_id' => $itemId])->select()->toArray();

        $selectFactors = array_column($selectRows, 'factor_id');

//        var_dump($selectFactors);


        foreach ($first as &$f){


            foreach ($f['children'] as $key=>&$s){

                foreach ($s['children'] as $k=>&$t){

                    if ($selectFactors && !in_array($t['id'], $selectFactors)){

                        $t['selected'] =0;

                        unset($s['children'][$k]);
                    }else{
                        $t['selected'] =1;
                    }
                }

            }
        }


        return json(['code' => 0, 'data' => $first, 'message' => 'OK']);
    }


    private function getChildren($factor) {
        $child = FactorModel::where(['status' => 1, 'pid' => $factor['id']])->field("id,name")->select()->toArray();
        if ($child) {
            $factor['children'] = $child;
            foreach ($child as &$v) {
                $v['children'] = $this->getChildren($v);
            }
        }
        return $child;
    }
}
