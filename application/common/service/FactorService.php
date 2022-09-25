<?php

namespace app\common\service;


use app\admin\model\Factor;
use app\admin\model\Factor as FactorModel;
use app\admin\model\FactorDetail as FactorDetailModel;
use app\admin\model\Items;
use app\admin\model\ItemsFactor;
use app\admin\model\ItemsFactor as ItemFactorModel;
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
        $data = static::factorData($itemId);
        return static::sortData($data);
    }

    /**
     * @brief  二级指标树
     * @param $itemId
     * @return array
     */
    public static function simpleTree($itemId) {
        // 查询已经选择的
        $selectFactors = [];
        if ($itemId) {
            $selectRows    = ItemsFactor::where(['item_id' => $itemId])->select()->toArray();
            $selectFactors = array_column($selectRows, 'factor_id');
        }

        // 查询所有的指标
        $fields     = ['id', 'pid', 'name'];
        $factorRows = FactorModel::where(['status' => 1])->field($fields)->select()->toArray();
        $topFactors = [];
        $sonFactors = [];
        foreach ($factorRows as $factorRow) {
            if ($factorRow['pid'] == 0) {
                $topFactors[] = $factorRow;
            } else {
                $sonFactors[] = $factorRow;
            }
        }
        foreach ($topFactors as &$topFactor) {
            foreach ($sonFactors as $sonFactor) {
                if ($topFactor['id'] == $sonFactor['pid']) {
                    foreach ($sonFactors as $son) {
                        if ($son['pid'] == $sonFactor['id']) {
                            if ($selectFactors && in_array($son['id'], $selectFactors)) {
                                $son['selected'] = 1;
                            } else {
                                $son['selected'] = 0;
                            }
                            $topFactor['child'][] = $son;
                        }
                    }
                }
            }
        }
        return $topFactors;
    }

    /**
     * @brief  获取所有的项目指标
     * @return array
     */
    public static function factorData($itemId = 0, $id = []): array {
        $where = ['f.status' => 1];
        $field = ['name', 'f.id', 'input_mode', 'option', 'coefficient', 'pid', 'method', 'meaning', 'calc_method', 'source', 'document', 'format_type'];
        $query = FactorModel::alias('f')->where($where);
        if ($id) {
            $query = $query->whereIn('f.id', $id);
        }
        $query = $query->field($field)->join('fa_factor_detail', 'f.id = fa_factor_detail.factor_id', 'left');
        $data  = $query->select()->toArray();

        // 查询已经选择的
        $selectFactors = [];
        if ($itemId) {
            $selectRows    = ItemsFactor::where(['item_id' => $itemId])->select()->toArray();
            $selectFactors = array_column($selectRows, 'factor_id');
        }

        foreach ($data as $key => &$value) {

            $value['id'] = (String)$value['id'];
            if ($selectFactors && in_array($value['id'], $selectFactors)) {
                $value['selected'] = 1;
            } else {
                $value['selected'] = 0;
            }
            $value['document'] = json_decode($value['document']);
            $inputModel        = strtoupper($value['input_mode']);
            if ($inputModel == 'A') {
                $value['option'] = json_decode($value['option'], true) ?? [];
            } elseif ($inputModel == 'D') {
                $value['option'] = '';
            } elseif ($inputModel == 'C') {
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

            $value['items'] = self::handleItem($value['id']);
        }
        return $data;
    }

    public static function handleItem($factorId){

        $item = ItemFactorModel::alias('if')
            ->join('fa_items i', 'i.id=if.item_id', 'left')
            ->field('i.name,i.images')
            ->where(['if.factor_id' => $factorId])
            ->where('i.status','>',0)
            ->limit(3)->select()->toArray();

        foreach ($item as &$v) {
            $v['images'] =  ImagesService::getBaseUrl() . $v['images'];
        }

        return $item;

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
            if (isset($value['coefficient'])) {
                $value['coefficient'] = json_decode($value['coefficient'], true) ?? [];
            }
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

        $list = FactorModel::where(['show_index' => 1, 'status' => 1])->field(['id', 'name', 'image'])->paginate($size, false, ['page' => $page])->toArray();
        if (isset($list['data'])) {
            foreach ($list['data'] as &$v) {
                $v['image'] =  ImagesService::getBaseUrl() . $v['image'];
            }
        }

        $data['total'] = $list['total'];
        $data['pages'] = (int)ceil($list['total'] / $size);
        $data['list']  = $list['data'];
        return $data;
    }
}
