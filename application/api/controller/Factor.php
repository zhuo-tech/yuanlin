<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\service\FactorService;
use app\common\service\ItemFactorService;
use think\Db;
use think\Env;
use think\Request;
use app\admin\model\FactorDetail as FactorDetailModel;
use app\admin\model\Factor as FactorModel;
use app\admin\model\ItemsFactor as ItemFactorModel;

use app\admin\model\Questions as QuestionsModel;
use app\admin\model\Items as ItemsModel;

/**
 * @title 指标
 * @description 接口说明
 */
class Factor extends Api {
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * @brief 指标树
     */
    public function tree(Request $request) {
        $itemId = $request->param('item_id', 0);
        $data   = FactorService::getFactorTree($itemId);
        return json(['code' => 0, 'data' => $data, 'message' => 'OK']);
    }

    /**
     * @brief 已选指标，不包含二级
     */
    public function simpleTree(Request $request) {
        $itemId = $request->param('item_id', 0);
        $data   = FactorService::simpleTree($itemId);
        return json(['code' => 0, 'data' => $data, 'message' => 'OK']);
    }


    public function simpleTree2(Request $request){

        $itemId = $request->param('item_id', 0);
        $data   = FactorService::simpleTree($itemId);

        $n =0;

        foreach ($data as &$vs){

            foreach ($vs['child'] as &$v){
                $detail = FactorDetailModel::get(['factor_id'=>$v['id']])->toArray();
                if($detail['option']){
                    $v['option'] = json_decode($detail['option']);
                }else{
                    $v['option'] = [];
                }

                $n = $n+1;

                $v['id'] =$n;
                $v['child'][] = $v;
                unset($v['option']);

                unset($v['selected']);
                unset($v['pid']);

            }

        }
        return json(['code' => 0, 'data' => $data, 'message' => 'OK']);

    }


    /**
     * @brief 获取保存的指标【没有用到】
     */
    public static function getSaveFactors(Request $request) {
        $itemId = $request->param('item_id', 0);
        $data   = FactorService::getFactorTree($itemId);
        return json(['code' => 0, 'data' => $data, 'message' => 'OK']);
    }

    public function factorTree() {
        $first = FactorModel::where(['status' => 1, 'pid' => 0])->field("id,name")->select()->toArray();
        foreach ($first as &$v) {
            $v['children'] = $this->getChildren($v);
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


    public function factorDetail(Request $request) {

        $factorId = $request->param('factor_id');

        $factor = FactorDetailModel::alias('fd')
                      ->join('factor f', 'fd.factor_id=f.id', 'left')
                      ->where(['fd.factor_id' => $factorId])->field('fd.*,f.name')->select()->toArray()[0];

        $factor['option']   = json_decode($factor['option']);
        $factor['document'] = json_decode($factor['document']);

        $question = QuestionsModel::field("*")->whereIn('id', $factor['questions_id'])->select()->toArray();
        foreach ($question as &$q) {
            $q['options'] = json_decode($q['options']);
        }

        $factor['questions'] = $question;

        $item = ItemFactorModel::alias('if')
            ->join('fa_items i', 'i.id=if.item_id', 'left')
            ->field('i.name,i.images')
            ->where(['if.factor_id' => $factorId])
            ->limit(3)->select()->toArray();

        foreach ($item as &$v) {
            $v['images'] = Env::get('app.baseurl', 'http://ies-admin.zhuo-zhuo.com') . $v['images'];
        }

        $factor['items'] = $item;

        return json(['code' => 0, 'message' => 'OK', 'data' => $factor]);

    }


    /**
     *
     * @brief 获取当前输入的指标
     */

    public function getItemFactor(Request $request) {

        $itemId = $request->param('item_id');

        $factorId = $request->param('current_factor_id');

        $preFactorId  = 0;
        $nextFactorId = 0;

        if($factorId==-1){
            //$this->error('已经是最后一项');

            return json_encode(['code'=>1,'message'=>'最后一项']);
        }

        if ($factorId) {

            $current = ItemFactorModel::field("id")
                           ->where(['item_id' => $itemId])
                           ->where(['factor_id' => $factorId])
                           ->select()->toArray()[0];

            $pre = ItemFactorModel::field("id,factor_id")
                ->where(['item_id' => $itemId])
                ->where('id', '<', $current['id'])
                ->order('id', 'desc')
                ->limit(1)
                ->select()->toArray();
            if ($pre) {
                $preFactorId = $pre[0]['factor_id'];
            }

            $next = ItemFactorModel::field("id,factor_id")
                ->where(['item_id' => $itemId])
                ->where('id', '>', $current['id'])
                ->order('id', 'asc')
                ->limit(1)
                ->select()->toArray();

            if ($next) {
                $nextFactorId = $next[0]['factor_id'];
            }else{
                $nextFactorId =-1;
            }

        } else {

            $current = ItemFactorModel::field("id,factor_id")
                ->where(['item_id' => $itemId])
                ->order('id', 'asc')
                ->limit(1)
                ->select()->toArray();

            if ($current) {

                $factorId = $current[0]['factor_id'];

            }

            $next = ItemFactorModel::field("id,factor_id")
                ->where(['item_id' => $itemId])
                ->where('id', '>', $current[0]['id'])
                ->order('id', 'asc')
                ->limit(1)
                ->select()->toArray();
            if ($next) {
                $nextFactorId = $next[0]['factor_id'];
            }else{
                $nextFactorId =-1;
            }

        }


        $factor = FactorDetailModel::alias('fd')
                      ->join('factor f', 'fd.factor_id=f.id', 'left')
                      ->where(['fd.factor_id' => $factorId])->field('fd.*,f.name')->select()->toArray()[0];

        $factor['option']   = json_decode($factor['option']);
        $factor['document'] = json_decode($factor['document']);

        $question = QuestionsModel::field("*")->whereIn('id', $factor['questions_id'])->select()->toArray();
        foreach ($question as &$q) {
            $q['options'] = json_decode($q['options']);
        }

        $factor['questions'] = $question;

        $item = ItemFactorModel::alias('if')
            ->join('fa_items i', 'i.id=if.item_id', 'left')
            ->field('i.name,i.images')
            ->where(['if.factor_id' => $factorId])
            ->limit(3)->select()->toArray();

        foreach ($item as &$v) {
            $v['images'] = Env::get('app.baseurl', 'http://ies-admin.zhuo-zhuo.com') . $v['images'];
        }

        $factor['items'] = $item;

        return json(['code' => 0, 'message' => 'OK','current'=>$factor['factor_id'], 'pre' => $preFactorId, 'next' => $nextFactorId, 'data' => $factor]);


    }

    /**
     *
     * @brief 保存指标
     */
    public function saveFactors(Request $request) {
        $itemId  = $request->param('item_id');
        $factors = $request->param('factors/a');

        $data = ItemFactorService::saveFactors((int)$itemId, $factors);
        return json(['code' => $data['error'], 'data' => [], 'message' => $data['message']]);
    }

    /**
     * @brief 确认指标
     */
    public function confirm(Request $request) {
        $itemId = $request->param('item_id', 0);
        $data   = ItemFactorService::doSure((int)$itemId);
        return json(['code' => $data['error'], 'data' => [], 'message' => $data['message']]);
    }

    /**
     * @brief 执行计算指标
     */
    public function execute(Request $request) {
        $itemId  = $request->param('item_id', 0);
        $factors = $request->param('factors/a', []);
        $data    = ItemFactorService::executeFactors((int)$itemId, $factors);
        return json(['code' => $data['error'], 'data' => [], 'message' => $data['message']]);
    }


    /**
     * @brief 获取项目指标
     */
    public function getItemFactors(Request $request) {
        $itemId = $request->param('item_id', 0);
        //$data   = FactorService::getSetFactors($itemId);

        $selectRows = ItemFactorModel::alias('if')->field("if.id,f.pid,f.name,fd.max,fd.min,fd.national_stand,format_type,if.result")
            ->join("fa_factor f", 'if.factor_id=f.id', 'left')
            ->join('fa_factor_detail fd', 'fd.factor_id = f.id', 'left')
            ->where(['if.item_id' => $itemId])->select()->toArray();
        $first      = FactorModel::where(['status' => 1, 'pid' => 0])
            ->field("id,name")->select()->toArray();
        foreach ($first as &$v) {
            $v['children'] = FactorModel::where(['status' => 1, 'pid' => $v['id']])
                ->field("id,name")->select()->toArray();
        }
        // 查询

        foreach ($first as &$f) {
            foreach ($f['children'] as $child) {
                foreach ($selectRows as $row) {
                    if ($row['pid'] == $child['id']) {
                        $result = floatval($row['result']);
                        $row['level'] = ($result - $row['min']) / ($row['max'] - $row['min']) / 2 * 10;
                        $f['select'][] = $row;
                    }
                }
            }
        }
        foreach ($first as &$ff) {
            unset($ff['children']);
        }

        $item =ItemsModel::get(['id'=>$itemId])
            ->toArray();
        $item['location'] = implode('',explode('/',$item['location']));

        return json(['code' => 0, 'data' => $first,'item'=>$item, 'message' => 'OK']);
    }
}
