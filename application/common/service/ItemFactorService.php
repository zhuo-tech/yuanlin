<?php

namespace app\common\service;


use app\admin\model\Factor as FactorModel;
use app\admin\model\FactorDetail as FactorDetailModel;
use app\admin\model\Items as ItemsModel;
use app\admin\model\ItemsFactor;
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
                $data[$key]['item_id']   = $itemId;
                $data[$key]['factor_id'] = $factor;
                $data[$key]['result']    = '';
                $data[$key]['status']    = 0;
            }
            if (ItemsModel::saveAll($data)) {
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

            ItemsModel::startTrans();
            foreach ($factors as $key => $factor) {
                $param  = json_encode($factor['param']);
                $result = FactorFormulaService::handle($itemId, $factor['id'], $factor['param']);
                ItemsModel::update(['param' => $param, 'result' => $result], ['item_id' => $itemId, 'factor_id' => $factor['id']]);
            }
            ItemsModel::commit();
            return ['error' => 0, 'message' => '保存成功', 'data' => []];
        } catch (\Exception $exception) {
            ItemsModel::rollback();
            return ['error' => 1, 'message' => $exception->getMessage(), 'data' => []];
        }
    }
}
