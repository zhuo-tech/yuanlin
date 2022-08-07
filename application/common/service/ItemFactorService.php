<?php

namespace app\common\service;


use app\admin\model\Factor as FactorModel;
use app\admin\model\FactorDetail as FactorDetailModel;
use app\admin\model\Items as ItemsModel;
use app\admin\model\ItemsFactor as ItemsFactorModel;
use think\exception\DbException;


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
                $itemFactor = ItemsModel::find(['item_id' => $itemId, 'factor_id' => $factor]);
                if (empty($itemFactor)) {
                    $data[$key]['item_id']   = $itemId;
                    $data[$key]['factor_id'] = $factor;
                    $data[$key]['result']    = '';
                    $data[$key]['status']    = 0;
                }
            }

            if (empty($data) || (ItemsModel::saveAll($data))) {
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
        //        try {
        $item = ItemsModel::get($itemId);
        if (empty($item)) {
            return ['error' => 1, 'message' => '项目不存在', 'data' => []];
        }

        $data = FactorDetailModel::where('factor_id' , 'in', [21, 41])->column('input_mode', 'factor_id');

        ItemsFactorModel::startTrans();
        foreach ($factors as $key => $factor) {
            $param = json_encode($factor['param']);
            $type  = strtoupper($data[$factor['id']]);
            if ($type == 'A') {
                $result = FactorFormulaService::handle($itemId, $factor['id'], $factor['param']);
            } else if ($type == 'C') {
                $result = 's';
            } else if ($type == 'D') {
                $result = $factor['param'];
            } else {
                $result = '';
            }
            ItemsFactorModel::update(['param' => $param, 'result' => $result], ['item_id' => $itemId, 'factor_id' => $factor['id']]);
        }
        ItemsFactorModel::commit();
        return ['error' => 0, 'message' => '保存成功', 'data' => []];
        //        } catch (\Exception $exception) {
        //            ItemsModel::rollback();
        //            return ['error' => 1, 'message' => $exception->getMessage(), 'data' => []];
        //        }
    }
}
