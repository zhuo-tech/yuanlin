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

        $question = QuestionsModel::field("*")->whereIn('id',$factor['questions_id'])->select()->toArray();
        foreach ($question as &$q){
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
     * @brief 保存指标
     */
    public function saveFactors(Request $request) {
        $itemId  = $request->param('item_id');
        $factors = $request->param('factors/a');
        var_dump($itemId, $factors);die();

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
     * @brief 获取保存的指标【没有用到】
     */
    public static function getSaveFactors(Request $request) {
        $itemId = $request->param('item_id', 0);
        $data   = FactorService::getFactorTree($itemId);
        return json(['code' => 0, 'data' => $data, 'message' => 'OK']);
    }

    /**
     * @brief 获取项目指标
     */
    public function getItemFactors(Request $request) {
        $itemId = $request->param('item_id', 0);
        $data   = FactorService::getSetFactors($itemId);
        return json(['code' => 0, 'data' => $data, 'message' => 'OK']);
    }
}
