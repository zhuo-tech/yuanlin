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

        //  根据分类查询 判断是一级分类还是二级分类
        if (isset($search['cid']) && $search['cid']) {
            //  判断是一级分类还是二级分类
            $cate = ItemsCate::find($search['cid']);
            if (isset($cate['pid']) && ($cate['pid'] == 0)) {
                $cates                 = ItemsCate::where(['pid' => $cate['id'], 'status' => 1])->column('id');
                $cates[] = $cate['id'];

                $where['item_cate_id'] = ['in', $cates];
            } else {
                $where['item_cate_id'] = ['=', $search['cid']];
            }
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
}
