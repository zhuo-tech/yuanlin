<?php

namespace app\common\service;


use app\admin\model\Factor as FactorModel;
use app\admin\model\FactorDetail as FactorDetailModel;
use app\admin\model\ItemsCate;
use app\admin\model\ItemsFactor;
use app\admin\model\Questions;
use think\exception\DbException;
use function GuzzleHttp\Psr7\str;


/**
 * 指数公式
 */
class ItemCategoryService {

    /**
     * 获取项目指标树
     * @return array
     */
    public static function category($itemId = 0) {
        $data = ItemsCate::where(['status' => 1])->field(['id', 'pid', 'name', 'label'])->select()->toArray();
        return static::sortData($data);
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

            if ($value['pid'] == $pid) {
                unset($data[$key]);
                $value['child'] = self::sortData($data, (int)$value['id']);
                $tree[]         = $value;
            }
        }
        return $tree;
    }
}
