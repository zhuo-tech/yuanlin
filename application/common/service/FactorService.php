<?php

namespace app\common\service;


use app\admin\model\Factor as FactorModel;
use app\admin\model\FactorDetail as FactorDetailModel;
use app\admin\model\ItemsFactor;
use think\exception\DbException;
use function GuzzleHttp\Psr7\str;


/**
 * 指数公式
 */
class FactorService {

    /**
     * 获取项目指标树
     * @return array
     */
    public static function getFactorTree($itemId) {
        // 查询顶级
        if ($itemId) {
            $data = static::getTreeByChild($itemId);
        } else {
            $data = static::factorData();
        }
        return static::sortData($data);
    }

    /**
     * @brief  获取所有的项目指标
     * @return array
     */
    public static function factorData($id = []): array {
        $where = ['f.status' => 1];
        $field = ['name', 'f.id', 'input_mode', 'option', 'coefficient', 'pid', 'method', 'meaning', 'calc_method', 'source'];
        $query = FactorModel::alias('f')->where($where);
        if ($id) {
            $query = $query->whereIn('f.id', $id);
        }

        $query = $query->field($field)->join('fa_factor_detail', 'f.id = fa_factor_detail.factor_id', 'left');

        return $query->select()->toArray();
    }

    /**
     * @brief 根据子级返回结构
     * @param $itemId
     */
    public static function getTreeByChild($itemId) {
        // 先查询所有的
        $selectRows    = ItemsFactor::where(['item_id' => $itemId, 'status' => 1])->select()->toArray();
        $selectFactors = array_column($selectRows, 'factor_id');
        $selectData    = static::factorData($selectFactors);

        $secondData = static::factorData(array_column($selectData, 'pid'));
        $oneData    = static::factorData(array_column($secondData, 'pid'));

        return array_merge($oneData, $secondData, $selectData);
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
