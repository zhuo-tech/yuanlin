<?php

namespace app\common\service;


use app\admin\model\ItemsCate;
use app\admin\validate\Items as ItemsValidate;
use app\admin\model\Items;
use function GuzzleHttp\Psr7\str;


/**
 * 项目
 */
class ItemService {

    /**
     * @brief 创建项目
     * @param $data
     * @return array
     */
    public static function saveItem($data) {
        $validate = new ItemsValidate();
        if (!$validate->scene('insert')->check($data)) {
            return ['error' => 1, 'message' => $validate->getError(), 'data' => []];
        }

        $model  = new Items($data);
        $result = $model->allowField(true)->save();
        if ($result) {
            return ['error' => 0, 'message' => '创建成功', 'data' => []];
        }
        return ['error' => 1, 'message' => '创建失败', 'data' => []];
    }

    /**
     * @分类项目列表
     * @param $search
     * @param $page
     * @param $fields
     * @return array
     */
    public static function cate($search, $page = 1, $fields = '*') {
        $page = ($page >= 1) ? $page : 1;

        $cid = ItemCategoryService::innermost($search['cid']);
        if ($cid) {
            $where['item_cate_id'] = ['in', $cid];
        }

        $where['status'] = 1;
        if ($search['keyword']) {
            $where['name'] = ['like', "%{$search['keyword']}%"];
        }

        if ($search['uid']) {
            $where['uid'] = ['=', $search['uid']];
        }

        $order         = 'id desc';
        $limit         = 10;
        $list          = Items::where($where)->field($fields)->orderRaw($order)->paginate($limit, false, ['page' => $page])->toArray();
        $data['list']  = $list['data'];
        $data['total'] = (int)ceil($list['total'] / $limit);
        return $data;
    }

    /**
     * @brief 根据指标查询
     * @param $search
     * @param $page
     * @return array
     * @throws \think\exception\DbException
     */
    public static function search($search, $page = 1) {
        $page = ($page >= 1) ? $page : 1;

        $fid = FactorService::innermost($search['fid']);
        if ($fid) {
            $where['fa_items_factor.factor_id'] = ['in', $fid];
        }
        $where['i.status'] = 1;
        if ($search['keyword']) {
            $where['name'] = ['like', "%{$search['keyword']}%"];
        }

        if ($search['uid']) {
            $where['uid'] = ['=', $search['uid']];
        }

        $order         = 'i.id desc';
        $limit         = 10;
        $fields        = 'i.*';
        $list          = Items::alias('i')->join('fa_items_factor', 'fa_items_factor.item_id = i.id', 'left')
                        ->where($where)->field($fields)->orderRaw($order)->group('i.id')->paginate($limit, false, ['page' => $page])->toArray();
        $data['total'] = $list['total'];
        $data['pages'] = (int)ceil($list['total'] / $limit);
        $data['list']  = $list['data'];
        return $data;
    }
}
