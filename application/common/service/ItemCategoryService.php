<?php

namespace app\common\service;


use app\admin\model\ItemsCate;


/**
 * 指数公式
 */
class ItemCategoryService {

    /**
     * 获取项目指标树
     * @return array
     */
    public static function category($itemId = 0) {
        $data = ItemsCate::where(['status' => 1])->field(['id', 'pid', 'name', 'type'])
                    ->order('sorts','asc')->select()->toArray();
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

    /**
     * @brief 根据ID返回子级ID
     * @param $cid
     */
    public static function innermost($cid) {
        $cid    = intval($cid);
        if ($cid <= 0) {
            return [];
        }
        return ItemsCate::where('id|pid', '=', $cid)->where(['status' => 1])->column('id,type,sorts');
    }
}
