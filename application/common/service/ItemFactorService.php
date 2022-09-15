<?php

namespace app\common\service;


use app\admin\model\FactorDetail as FactorDetailModel;
use app\admin\model\Items as ItemsModel;
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
                }
            }
            if (empty($data) || (ItemsFactorModel::insertAll($data))) {
                return ['error' => 0, 'message' => 'OK', 'data' => []];
            }
            return ['error' => 1, 'message' => '保存失败', 'data' => []];
        } catch (\Exception $exception) {
            return ['error' => 1, 'message' => $exception->getMessage(), 'data' => []];
        }
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

            if (ItemsModel::update(['status' => 1], ['item_id' => $itemId])) {
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

            $data  = FactorDetailModel::where('factor_id', 'in', array_column($factors, 'id'))->column('input_mode', 'factor_id');
            ItemsFactorModel::startTrans();
            foreach ($factors as $key => $factor) {
                $param = json_encode($factor['param']);
                $type  = strtoupper($data[$factor['id']]);
                if ($type == 'A') {
                    $result = FactorFormulaService::handle($itemId, $factor['id'], $factor['param']);
                } else if ($type == 'D') {
                    $result = $factor['param'];
                } else if ($type == 'C') {
                    $result = array_sum(array_column($factor['param'], 'score')) / count($factor['param']);
                } else {
                    $result = '';
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
}
