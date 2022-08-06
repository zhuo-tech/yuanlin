<?php

namespace app\common\service;


use app\admin\model\Factor as FactorModel;
use app\admin\model\FactorDetail as FactorDetailModel;
use app\admin\model\ItemsFactor;
use think\exception\DbException;


/**
 * 指数公式
 */
class FactorService {

    /**
     * 获取项目指标树
     * @return array
     */
    public static function getFactorTree() {
        // 查询顶级
        $data = static::factorData();
        return static::sortData($data);
    }

    /**
     * @brief  获取所有的项目指标
     * @return array
     */
    public static function factorData(): array {
        $field = ['name', 'f.id', 'input_mode', 'option', 'coefficient', 'pid', 'method', 'meaning', 'calc_method', 'source'];
        return FactorModel::alias('f')->where(['f.status' => FactorModel::STATUS_ACTIVE])->field($field)
            ->join('fa_factor_detail', 'f.id = fa_factor_detail.factor_id', 'left')->select()->toArray();
    }

    /**
     * 组织树
     * @param $data
     * @param int $pid
     * @return array
     */
    public static function sortData($data, int $pid = 0): array {
        $tree = [];
        foreach ($data as $key => $value) {
            $keys = array_keys($value);
            foreach ($keys as $k) {
                if (is_null($value[$k])) {
                    $value[$k] = '';
                }
            }
            $value['option']      = json_decode($value['option'], true) ?? [];
            $value['coefficient'] = json_decode($value['coefficient'], true) ?? [];
            if ($value['pid'] == $pid) {
                unset($data[$key]);
                $value['child'] = self::sortData($data, (int)$value['id']);
                $tree[]         = $value;
            }
        }
        return $tree;
    }
}
