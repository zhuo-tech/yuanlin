<?php

namespace app\common\service;


use app\admin\model\Factor as FactorModel;
use app\admin\model\FactorDetail as FactorDetailModel;
use app\admin\model\Items as ItemsModel;
use app\admin\model\ItemsFactor as ItemFactorModel;
use app\admin\model\ItemsFactor as ItemsFactorModel;


/**
 * 指数公式
 */
class ItemFactorService {

    /**
     * @brief 保存指标
     * @param $itemId int 项目ID
     * @param $factors array 指标ID
     * @return array
     */
    public static function saveFactors(int $itemId, array $factors): array {
        try {
            $item = ItemsModel::get($itemId);
            if (empty($item)) {
                return ['error' => 1, 'message' => '项目不存在', 'data' => []];
            }
            if(empty($factors))  return ['error' => 1, 'message' => '指标不能为空', 'data' => []];
            $factors = self::getFactorIdBySorts($factors);
            $item->save(['status'=>2,'factors'=>json_encode($factors)]);
            ItemsFactorModel::where(['item_id' => $itemId])->update(['status'=>-1]);

            $data = [];
            foreach ($factors as $key => $factor) {
                $itemFactor = ItemsFactorModel::where(['item_id' => $itemId, 'factor_id' => $factor])->select()->toArray();
                if (empty($itemFactor)) {
                    $data[$key]['item_id']     = $itemId;
                    $data[$key]['factor_id']   = $factor;
                    $data[$key]['result']      = '';
                    $data[$key]['sample_size'] = $factor['sample_size'] ?? 0;
                    $data[$key]['invest_time'] = $factor['invest_time'] ?? 0;
                    $data[$key]['status']      = 0;
                    $data[$key]['create_time'] = time();
                }else{
                  ItemsFactorModel::where(['item_id' => $itemId, 'factor_id' => $factor])->update(['status'=>0]);

                }
            }
            if (empty($data) || (ItemsFactorModel::insertAll($data))) {
                return ['error' => 0, 'message' => 'OK', 'data' => []];
            }
            return ['error' => 1, 'message' => '保存失败', 'data' => []];
        } catch (\Exception $exception) {
            throw $exception;
            return ['error' => 1, 'message' => $exception->getMessage(), 'data' => []];
        }
    }

    public static function getFactorIdBySorts($factors){

        $result = FactorModel::field('id,sorts')
            ->whereIn('id',$factors)
            ->order('sorts','asc')->select()->toArray();
        return array_column($result,'id');

    }

    /**
     * @brief 确认指标
     * @param $itemId int 项目ID
     * @return array
     */
    public static function doSure(int $itemId): array {
        try {
            $item = ItemsModel::get($itemId);
            if (empty($item)) {
                return ['error' => 1, 'message' => '项目不存在', 'data' => []];
            }

            if (ItemsModel::update(['status' => 1], ['id' => $itemId])) {
                return ['error' => 0, 'message' => 'OK', 'data' => []];
            }
            return ['error' => 1, 'message' => '操作失败', 'data' => []];
        } catch (\Exception $exception) {
            return ['error' => 1, 'message' => $exception->getMessage(), 'data' => []];
        }
    }


    /**
     * @brief 执行指标数据
     * @param $itemId int 项目ID
     * @param $factors array 指标ID和参数
     * @return array
     */
    public static function executeFactors(int $itemId, array $factors): array {
        try {
            $item = ItemsModel::get($itemId);
            if (empty($item)) {
                return ['error' => 1, 'message' => '项目不存在', 'data' => []];
            }

            $item->save(['status'=>3]);

            $data = FactorDetailModel::where('factor_id', 'in', array_column($factors, 'id'))->column('input_mode', 'factor_id');
            ItemsFactorModel::startTrans();
            foreach ($factors as $key => $factor) {
                $param = json_encode($factor['param']);
                $type  = strtoupper($data[$factor['id']]);
                if ($type == 'A') {
                    $result = FactorFormulaService::handle($itemId, $factor['id'], $factor['param']);
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
                ItemsFactorModel::where(['item_id' => $itemId, 'factor_id' => $factor['id']])->update(['param' => $param, 'result' => $result]);
            }
            ItemsFactorModel::commit();
            return ['error' => 0, 'message' => '结果计算完成', 'data' => []];
        } catch (\Exception $exception) {
            ItemsModel::rollback();
            return ['error' => 1, 'message' => $exception->getMessage(), 'data' => []];
        }
    }


    public static function itemReport(int $itemId,$keyword){

        $item = ItemsModel::get($itemId);
        if (empty($item)) {
            return ['error' => 1, 'message' => '项目不存在', 'data' => []];
        }

        $item->save(['status'=>4]);


        $where['if.item_id'] = $itemId;

        if($keyword){
            $where['f.name'] = ['like', "%{$keyword}%"];
        }

        $selectRows = ItemFactorModel::alias('if')->field("if.id,f.pid,f.name,fd.max,fd.min,fd.national_stand,format_type,if.result")
            ->join("fa_factor f", 'if.factor_id=f.id', 'left')
            ->join('fa_factor_detail fd', 'fd.factor_id = f.id', 'left')
            ->where($where)->select()->toArray();

        $first      = FactorModel::where(['status' => 1, 'pid' => 0])
            ->field("id,name")->select()->toArray();
        foreach ($first as &$v) {
            $v['children'] = FactorModel::where(['status' => 1, 'pid' => $v['id']])
                ->field("id,name")->select()->toArray();
        }
        // 查询


        $all = ['id'=>0,'name'=>'全部'];

        // row['result'] = 0 前端类型报错

        foreach ($first as &$f) {
            foreach ($f['children'] as $child) {
                foreach ($selectRows as $row) {
                    if ($row['pid'] == $child['id']) {
                        $result = floatval($row['result']);
                        $row['level'] = ($result - $row['min']) / ($row['max'] - $row['min']) / 2 * 10;
                        $f['select'][] = $row;

                        $all['select'][] =$row;
                    }
                }
            }
        }

        $echart = [];
        foreach ($first as &$ff) {
            unset($ff['children']);
            //$array = array_column($ff['select'],'level');
            // array_push($echart,count($array)>0? array_sum($array)/count($array):0);
        }

        array_unshift($first,$all);

        $item =ItemsModel::get(['id'=>$itemId])
            ->toArray();
        $item['location'] = implode('',explode('/',$item['location']));


        return ['error' => 0, 'message' => '', 'first' => $first,'item'=>$item,'echart'=>$echart];

    }
}
