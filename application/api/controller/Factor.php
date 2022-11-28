<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\service\FactorFormulaService;
use app\common\service\FactorService;
use app\common\service\ImagesService;
use app\common\service\ItemFactorService;
use think\Cache;
use think\Db;
use think\Env;
use think\Request;
use app\admin\model\FactorDetail as FactorDetailModel;
use app\admin\model\Factor as FactorModel;
use app\admin\model\ItemsFactor as ItemFactorModel;

use app\admin\model\Questions as QuestionsModel;
use app\admin\model\Items as ItemsModel;
use think\response\Json;

/**
 * @title 指标
 * @description 接口说明
 */
class Factor extends Api {
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];
    protected $searchedFactor = [];

    public function hotFactor(){

        $data = FactorModel::where(['status'=>1])
            ->field('id,name')
            ->order('numbers','desc')
            ->limit(8)
            ->select()->toArray();
        $this->success('请求成功',$data);

    }
    /**
     * @brief 指标树
     */
    public function tree(Request $request) {
        $itemId  = $request->param('item_id', 0);
        $keyword = $request->param('keyword', '');
        $id = $request->param('id','');
        // 缓存
        $cacheKey = md5( 'factorTree');
        $cacheVal = Cache::get($cacheKey);
        if (0) {
            $data = json_decode($cacheVal, true);
        } else {
            $data = FactorService::getFactorTree($itemId);
            Cache::set($cacheKey, json_encode($data, JSON_UNESCAPED_UNICODE), 3600 * 24);
        }

        if ($keyword) {
            $data = $this->search($data, $keyword);
        }

        if($id)$data= $this->getById($data,$id);
        return json(['code' => 0, 'data' => $data, 'searched'=>$this->searchedFactor,'message' => 'OK']);
    }

    public function getById($data, $id){

        foreach ($data as $key => &$f) {
            foreach ($f['child'] as $key2 => &$s) {
                foreach ($s['child'] as $key3 => $t) {

                    if ($t['id']== $id) {
                        $this->searchedFactor= [$id];
                    } else {
                        unset($s['child'][$key3]);
                    }
                }
            }
        }

        return $data;

    }

    public function search($data, $keyword) {

        foreach ($data as $key => &$f) {
            foreach ($f['child'] as $key2 => &$s) {
                foreach ($s['child'] as $key3 => $t) {
                    if (stristr($t['name'], $keyword)) {
                        array_push($this->searchedFactor,$t['id']);
                    } else {
                        unset($s['child'][$key3]);
                    }
                }
            }
        }

        return $data;

    }

    /**
     * @brief 已选指标，不包含二级
     */
    public function simpleTree(Request $request) {
        $itemId = $request->param('item_id', 0);
        $data   = FactorService::simpleTree($itemId);
        $cid    = $request->param('cid', 0);
        $n      = 0;

        //var_dump($data);
        foreach ($data as $key => &$vs) {
            if ($cid) {
                if ($cid == $vs['id']) {

                    //var_dump($vs['child']);
                    foreach ($vs['child'] as $k => &$v) {
                        if ($v['selected'] == 1) {
                            $detail = FactorDetailModel::get(['factor_id' => $v['id']])->toArray();
                            if ($detail['option']) {
                                $v['option'] = json_decode($detail['option']);
                            } else {
                                $v['option'] = [];
                            }
                            $n       = $n + 1;
                            $v['number'] = $n;

                        } else {
                            unset($vs['child'][$k]);
                        }
                    }

                    $vs['child'] =  array_values($vs['child']);
                } else {
                    unset($data[$key]);
                }
            } else {
                foreach ($vs['child'] as $k => &$v) {
                    if ($v['selected'] == 1) {
                        $detail = FactorDetailModel::get(['factor_id' => $v['id']])->toArray();
                        if ($detail['option']) {
                            $v['option'] = json_decode($detail['option']);
                        } else {
                            $v['option'] = [];
                        }
                        $n       = $n + 1;
                        $v['number'] = $n;
                        $v['input_mode']= $detail['input_mode'];

                    } else {
                        unset($vs['child'][$k]);
                    }
                }

                $vs['child'] =  array_values($vs['child']);

            }
        }

        $data = $this->handleSimpleData($data);


        $first = FactorModel::where(['status' => 1, 'pid' => 0])->field("id,name,en_name")->select()->toArray();

        array_unshift($first, ['name' => '全部','en_name'=>'all', 'id' => 0]);

        return json(['code' => 0, 'first' => $first, 'data' => $data, 'message' => 'OK']);
    }


    /**
     * @brief 已选指标结果，不包含二级
     */
    public function simpleTreeResult(Request $request) {

        $itemId = $request->param('item_id', 0);
        $data   = FactorService::simpleTree($itemId);
        $n      = 0;
        foreach ($data as $key => &$vs) {
            foreach ($vs['child'] as $k => &$v) {
                if ($v['selected'] == 1) {
                    $detail = FactorDetailModel::get(['factor_id' => $v['id']])->toArray();

                    $itemFactor = ItemFactorModel::get(['factor_id' => $v['id'], 'item_id' => $itemId])->toArray();

                    if ($itemFactor['param']) {

                        $param  = json_decode($itemFactor['param'], 1);
                        $detail['option'] = json_decode($detail['option'], 1);

                        $detailss = $this->getOptionParam($detail, $param,2);

                        $v['option'] =$detailss['option'];
                    } else {

                        if ($detail['option']) {
                            $v['option'] = json_decode($detail['option'], 1);
                        } else {
                            $v['option'] = [];
                        }
                    }

                    $n       = $n + 1;
                    $v['number'] = $n;
                    $v['input_mode'] = $detail['input_mode'];

                } else {
                    unset($vs['child'][$k]);
                }
            }
            $vs['child'] = array_values($vs['child']);
        }

        $data = $this->handleSimpleData($data);

        return json(['code' => 0, 'data' => $data, 'message' => 'OK']);

    }

    public function getOptionParam($factor,$param,$position=1){

        if($factor['input_mode']=="A"){
            $factor['option'] = $this->handleOptionParam($factor['option'],$param);
        }elseif($factor['input_mode']=="C"){
            if($position==2)$factor['questions'] = [];
            $factor['questions'] = $this->handleQuestionOptionParam($factor['questions'],$param);
            $factor['option'] = [];
        }elseif ($factor['input_mode']=="B"){
            $factor['option'] = $this->handleFileOptionParam($factor['option'],$param,$position);
        }

        return $factor;

    }

    public function handleOptionParam($options, $params) {

        foreach ($options as &$option) {
            if(isset($params[$option['var']])){
                $option['value'] = $params[$option['var']];
            }else{
                $option['value'] = '';
            }

        }
        return $options;
    }

    public function handleFileOptionParam($options, $params,$position=1){

        foreach ($options as &$option) {
            if(isset($params[$option['var']]) ){
                if($option['tip']=='3'){
                    $option['value'] = $params[$option['var']];
                }elseif ($option['tip']=='2'){
                    $option['value'] = $params[$option['var']];
                }
                else{
                    $index = $option['var'].'name';
                    if($position==2){
                        $option['value'] = isset($params[$index])?$params[$index]:'';
                    }else{
                        $option['value'] = $params[$option['var']];
                    }

                    $option[$index] = isset($params[$index])?$params[$index]:'';
                }
            }else{
                $option['value'] = '';
            }
        }
        return $options;

    }

    public function handleQuestionOptionParam($questions, $params){

        foreach ($questions as $km=>&$question) {

            foreach ($questions[$km]['options'] as $kn=>&$option){

                foreach ($params as $p){

                    if($p['var']==$questions[$km]['options'][$kn]['var']&&$p['id']==$question['id']){

                        $questions[$km]['options'][$kn]['value'] = isset($p['value'])?(int)$p['value']:0;

                    }
                }

            }


        }
        return $questions;

    }

    public function handleSimpleData($data) {

        $record = [];
        if (count($data) == 1) {
            foreach ($data as $ds) {
                array_push($record, $ds);

            }
        } else {
            return $data;
        }

        return $record;
    }

    public function handleSelectFactor($vs, $n) {


        foreach ($vs['child'] as $k => &$v) {
            if ($v['selected'] == 1) {
                $detail = FactorDetailModel::get(['factor_id' => $v['id']])->toArray();
                if ($detail['option']) {
                    $v['option'] = json_decode($detail['option']);
                } else {
                    $v['option'] = [];
                }
                $n       = $n + 1;
                $v['id'] = $n;

            } else {
                unset($vs['child'][$k]);
            }
        }

        return $vs;

    }


    public function simpleTree2(Request $request) {

        $itemId = $request->param('item_id', 0);

        $cid = $request->param('cid', 0);

        $data = FactorService::simpleTree($itemId);
        $n    = 0;
        foreach ($data as $key => &$vs) {
            if ($cid) {
                if ($cid == $vs['id']) {
                    foreach ($vs['child'] as $k => &$v) {
                        if ($v['selected'] == 1) {
                            $detail = FactorDetailModel::get(['factor_id' => $v['id']])->toArray();
                            if ($detail['option']) {
                                $v['option'] = json_decode($detail['option']);
                            } else {
                                $v['option'] = [];
                            }
                            $n            = $n + 1;
                            $v['id']      = $n;
                            $v['child'][] = $v;
                            unset($v['option']);
                            unset($v['selected']);
                            unset($v['pid']);
                        } else {
                            unset($vs['child'][$k]);
                        }
                    }

                } else {
                    unset($data[$key]);
                }

            } else {

                foreach ($vs['child'] as $k => &$v) {
                    if ($v['selected'] == 1) {
                        $detail = FactorDetailModel::get(['factor_id' => $v['id']])->toArray();

                        if ($detail['option']) {
                            $v['option'] = json_decode($detail['option']);
                        } else {
                            $v['option'] = [];
                        }
                        $n            = $n + 1;
                        $v['id']      = $n;
                        $v['child'][] = $v;
                        unset($v['option']);
                        unset($v['selected']);
                        unset($v['pid']);
                    } else {

                        unset($vs['child'][$k]);
                    }

                }

            }

        }

        $first = FactorModel::where(['status' => 1, 'pid' => 0])->field("id,name")->select()->toArray();

        array_unshift($first, ['name' => '全部','en_name'=>'all', 'id' => 0]);

        return json(['code' => 0, 'first' => $first, 'data' => $data, 'message' => 'OK']);

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

        $cacheKey = md5( 'factorTree2');
        $cacheVal = Cache::get($cacheKey);
        if($cacheVal){

            $data = json_decode($cacheVal, true);
        }else{
            $first = FactorModel::where(['status' => 1, 'pid' => 0])->field("id,name,en_name")->select()->toArray();
            foreach ($first as &$v) {
                $v['children'] = $this->getChildren($v);
            }
            $data = $first;
            Cache::set($cacheKey, json_encode($data, JSON_UNESCAPED_UNICODE), 3600 * 24);
        }

        return json(['code' => 0, 'data' => $data, 'message' => 'OK']);
    }


    private function getChildren($factor) {
        $child = FactorModel::where(['status' => 1, 'pid' => $factor['id']])->field("id,name,en_name")->select()->toArray();
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
            $v['images'] = ImagesService::getBaseUrl() . $v['images'];
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

        if ($factorId == -1) {
            //$this->error('已经是最后一项');
            return json_encode(['code' => 1, 'message' => '最后一项']);
        }

        $item = ItemsModel::get($itemId);
        if(!$item){
            return json_encode(['code' => 1, 'message' => '项目不存在']);
        }

        $item = $item->toArray();
        $factors = json_decode($item['factors'],1);

        $curNextPre = $this->getCurNextPre($factorId,$factors);

        $factorId = $curNextPre['current'];


//        if ($factorId) {
//
//            $current = ItemFactorModel::field("id")
//                           ->where(['item_id' => $itemId])
//                           ->where(['factor_id' => $factorId])
//                           ->select()->toArray()[0];
//
//            $pre = ItemFactorModel::field("id,factor_id")
//                ->where(['item_id' => $itemId])
//                ->where('id', '<', $current['id'])
//                ->order('id', 'desc')
//                ->limit(1)
//                ->select()->toArray();
//            if ($pre) {
//                $preFactorId = $pre[0]['factor_id'];
//            }
//
//            $next = ItemFactorModel::field("id,factor_id")
//                ->where(['item_id' => $itemId])
//                ->where('id', '>', $current['id'])
//                ->order('id', 'asc')
//                ->limit(1)
//                ->select()->toArray();
//
//            if ($next) {
//                $nextFactorId = $next[0]['factor_id'];
//            } else {
//                $nextFactorId = -1;
//            }
//
//        }
//        else {
//
//            $current = ItemFactorModel::field("id,factor_id")
//                ->where(['item_id' => $itemId])
//                ->order('id', 'asc')
//                ->limit(1)
//                ->select()->toArray();
//
//            if ($current) {
//
//                $factorId = $current[0]['factor_id'];
//
//            }
//
//            $next = ItemFactorModel::field("id,factor_id")
//                ->where(['item_id' => $itemId])
//                ->where('id', '>', $current[0]['id'])
//                ->order('id', 'asc')
//                ->limit(1)
//                ->select()->toArray();
//            if ($next) {
//                $nextFactorId = $next[0]['factor_id'];
//            } else {
//                $nextFactorId = -1;
//            }
//
//        }


        $factor = FactorDetailModel::alias('fd')
            ->join('factor f', 'fd.factor_id=f.id', 'left')
            ->where(['fd.factor_id' => $factorId])->field('fd.*,f.name')->select()->toArray()[0];


        $factor['document'] = json_decode($factor['document']);

        $question = QuestionsModel::field("*")->whereIn('id', $factor['questions_id'])->select()->toArray();
        foreach ($question as &$q) {
            $q['options'] = json_decode($q['options'],1);
        }

        $factor['questions'] = $question;

        $factor['question_link'] = ImagesService::getBaseUrl().$factor['question_link'];

        $itemFactor = ItemFactorModel::get(['factor_id' => $factorId, 'item_id' => $itemId])->toArray();

        $factor['option']   = json_decode($factor['option'],1);
        if($itemFactor &&$itemFactor['param']){
            $param = json_decode($itemFactor['param'],1);

            $factor = $this->getOptionParam($factor,$param);



            $factor['formed']=1;
        }
        else{
            $factor['formed'] =0;
        }


        $factor['sample_size'] = (string)$itemFactor['sample_size'];

        $factor['year'] = '';
        $factor['month'] = '';
        $factor['day'] = '';

        if($itemFactor['invest_time']) {
            $dates = explode('-', $itemFactor['invest_time']);
            $factor['year'] = $dates[0];
            if (count($dates)>1){
                $factor['month'] = $dates[1];
            }

            if (count($dates)>2){
                $factor['day'] = $dates[2];
            }
        }



        return json(['code' => 0, 'message' => 'OK', 'current' => $factor['factor_id'], 'pre' => $curNextPre['pre'], 'next' => $curNextPre['next'], 'data' => $factor]);


    }

    public function getCurNextPre($factorId,$factors){
        $arr = [];

        $length = count($factors);

        $index = 0;

        if(!$factorId){

            $arr['current'] =$factors[0];
            if(isset($factors[1])){
                $arr['next'] =$factors[1];
            }else{
                $arr['next'] =-1;
            }
            $arr['pre'] =0;

        }else{
            $arr['current'] =$factorId;
            foreach ($factors as $key=>$v){
                if($v==$factorId){
                    $index =$key;
                }
            }
            if($index==0){
                $arr['pre'] =0;
                if($length==1){
                    $arr['next'] =-1;
                }else{
                    $arr['next'] =$factors[$index+1];
                }
            }elseif($index==$length-1){
                $arr['pre'] = $factors[$index-1];
                $arr['next'] =-1;
            }else{
                $arr['pre'] = $factors[$index-1];
                $arr['next'] =$factors[$index+1];
            }
        }

        return $arr;
    }

    /**
     *
     * @brief 保存指标
     */
    public function saveFactors(Request $request) {
        $itemId  = $request->param('item_id');
        $factors = $request->param('factors/a');

        //return json(['code' => 1, 'data' => [], 'message' => 1]);

        $data = ItemFactorService::saveFactors((int)$itemId, $factors);
        return json(['code' => $data['error'], 'data' => ['images'=>'http://ies-admin.zhuo-zhuo.com/uploads/20221104/de85c865d8b25e7bca360a06834d044c.png'], 'message' => $data['message']]);
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
     * @brief b 开始计算
     */

    public function saveFactor(Request $request){

        $item_id  = $request->param('item_id', '');
        $params = $request->param('params/a', []);

//        var_dump($item_id);
//        var_dump($params);die;


        $this->success('success',['image'=>'http://www.lpes.com.cn//uploads/20221021/036e8dc74d4403fcd1aed57e3194a31d.jpg']);
    }

    /**
     * @brief 执行计算指标
     */
    public function execute(Request $request) {
        $itemId  = $request->param('item_id', 0);
        $factors = $request->param('factors/a', []);

        //return json(['code' => -1, 'data' => [], 'message' => '']);

        $simple_size  = $request->param('sample_size', 0);

        $invest_time  = $request->param('invest_time', 0);
        $data    = ItemFactorService::executeFactors((int)$itemId, $factors,$simple_size,$invest_time);
        return json(['code' => $data['error'], 'data' => [], 'message' => $data['message']]);
    }


    public function testExecute(Request $request){

        $itemId  = $request->param('item_id', 0);

        $factorId  = $request->param('factor_id', 0);

        $factor = FactorDetailModel::get(['factor_id'=>$factorId])->toArray();

        $itemFactor = ItemFactorModel::get(['item_id'=>$itemId,'factor_id'=>$factorId])->toArray();

        $type = $factor['input_mode'];

        var_dump($type);

        var_dump($itemFactor['param']);


        if ($type == 'A') {
            $result = FactorFormulaService::handle($itemId, $factorId, json_decode($itemFactor['param'],1));
            var_dump($result);die;
        } else if ($type == 'D') {
            $result = $factor['param'];
        } else if ($type == 'C') {
            // 类型C是将所有的问题按照选择的选项的占比乘以分值再除以问题个数
            $sum = 0;
            foreach ($factor['param'] as $_param) {
                $sum += ($_param['score'] * $_param['value'] / 100);
            }
            $result = $sum / count($factor['param']);
        } else {
            $result = 0;
        }

        var_dump($result);die;


    }


    /**
     * 指标报告接口
     * @brief 获取项目指标
     *
     */
    public function getItemFactors(Request $request) {
        $itemId  = $request->param('item_id', 0);
        $keyword = $request->param('keyword', 0);
        //$data   = FactorService::getSetFactors($itemId);
        $data = ItemFactorService::itemReport($itemId, $keyword);
        return json(['code' => $data['error'], 'item' => $data['item'], 'echart' => $data['echart'], 'data' => $data['first'], 'message' => 'OK']);
    }
}