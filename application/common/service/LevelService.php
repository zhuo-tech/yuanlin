<?php

namespace app\common\service;


use app\admin\model\FactorDetail as FactorDetailModel;
use think\exception\DbException;


/**
 * 计算等级
 */
class LevelService {

    /**
     * @brief  计算等级
     * @throws DbException
     */
    public static function handle($factorId, $value) {
        try {
            // 查询factor_detail
            $factorDetail = FactorDetailModel::where(['factor_id' => $factorId])->find()->toArray();
            return ($value - $factorDetail['min']) / ($factorDetail['max'] - $factorDetail['min']) / 2 * 10;
        } catch (\Exception $exception) {
            return [];
        }
    }
}
