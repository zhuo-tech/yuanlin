<?php

namespace app\common\service;


use app\admin\model\Factor;
use app\admin\model\Factor as FactorModel;
use app\admin\model\FactorDetail as FactorDetailModel;
use app\admin\model\ItemsFactor;
use app\admin\model\Questions;
use think\Env;
use think\exception\DbException;


/**
 * 指数公式
 */
class FactorService {

    /**
     * 获取项目指标树
     * @return array
     */
    public static function getFactorTree($itemId = 0) {
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
        $field = ['name', 'f.id', 'input_mode', 'option', 'coefficient', 'pid', 'method', 'meaning', 'calc_method', 'source', 'document'];
        $query = FactorModel::alias('f')->where($where);
        if ($id) {
            $query = $query->whereIn('f.id', $id);
        }

        $query = $query->field($field)->join('fa_factor_detail', 'f.id = fa_factor_detail.factor_id', 'left');

        $data = $query->select()->toArray();

        foreach ($data as $key => &$value) {
            $inputModel = strtoupper($value['input_mode']);
            if ($inputModel == 'A') {
                $value['option'] = json_decode($value['option'], true) ?? [];
            } elseif ($inputModel == 'C') {
                $value['option'] = '';
            } elseif ($inputModel == 'D') {
                $questions       = json_decode($value['option']);
                $questionOptions = [];
                if ($questions) {
                    $questionOptions = Questions::whereIn('id', $questions)->field(['id', 'title', 'options'])->select()->toArray();
                    foreach ($questionOptions as &$questionOption) {
                        $questionOption['options'] = json_decode($questionOption['options'], true);
                    }
                }
                $value['option'] = $questionOptions;
            }
        }
        return $data;
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
            $value['coefficient'] = json_decode($value['coefficient'], true) ?? [];
            if ($value['pid'] == $pid) {
                unset($data[$key]);
                $value['child'] = self::sortData($data, (int)$value['id']);
                $tree[]         = $value;
            }
        }
        return $tree;
    }

    /**
     * @brief 根据ID查询指标ID
     * @param $id
     */
    public static function innermost($id) {
        $factor = Factor::find($id);
        $data   = [];
        if (empty($factor)) {
            return [];
        }
        if ($factor['pid'] == 0) {
            $factorIds = Factor::where(['pid' => $id])->column('id');
            if (empty($factorIds)) {
                return [];
            }
            $map['pid'] = ['in', $factorIds];
            $data       = Factor::whereIn('id', $factorIds)->whereOr($map)->where(['status' => 1])->column('id');
        } else {
            $data = Factor::where(['id' => $id])->whereOr(['pid' => $id])->where(['status' => 1])->column('id');
        }
        return $data;
    }

    /**
     * @brief 精选指标
     * @param $page
     * @return array
     * @throws \think\exception\DbException
     */
    public static function selected($page = 1, $size = 10) {
        $page = ($page >= 1) ? $page : 1;

        $list   = FactorModel::where(['show_index' => 1, 'status' => 1])->field(['id', 'name', 'image'])->paginate($size, false, ['page' => $page])->toArray();

        foreach ($list['data'] as &$v) {
            $v['image'] = Env::get('app.baseurl', 'http://ies-admin.zhuo-zhuo.com') . $v['image'];
        }
        $data['total'] = $list['total'];
        $data['pages'] = (int)ceil($list['total'] / $size);
        $data['list']  = $list['data'];
        return $data;
    }
}
