<?php

namespace app\common\service;


use app\admin\model\FactorDetail as FactorDetailModel;
use think\exception\DbException;


/**
 * 计算等级
 */
class LevelService {

    /**
     * @throws DbException
     */
    public static function handle($itemId, $factorId, $value) {
        try {
            // 查询factor_detail
            $factorDetail = FactorDetailModel::where(['factor_id' => $factorId])->find()->toArray();
            return ($value - $factorDetail['min']) / ($factorDetail['max'] - $factorDetail['min']) / 2 * 10;
        } catch (\Exception $exception) {
            return [];
        }
    }
}
