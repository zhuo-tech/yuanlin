<?php

namespace app\common\service;


use app\admin\validate\Items as ItemsValidate;
use app\admin\model\Items;
use app\admin\model\City as cityModel;
use think\Env;


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

        $locations = [];
        $province = cityModel::get(['area_code'=>$data['province']])->toArray();
        array_push($locations,$province['area_name']);
        $city =  cityModel::get(['area_code'=>$data['city']])->toArray();
        array_push($locations,$city['area_name']);
        $region =  cityModel::get(['area_code'=>$data['area']])->toArray();
        array_push($locations,$region['area_name']);
        $data['location'] =implode('/',$locations);

        $model  = new Items($data);
        $result = $model->allowField(true)->save();
        if ($result) {
            return ['error' => 0, 'message' => '创建成功', 'data' => ['id' => $model->id]];
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
            $where['user_id'] = ['=', $search['uid']];
        }

        $order         = 'id asc';
        $limit         = 10;
        $list          = Items::where($where)->field($fields)->orderRaw($order)->paginate($limit, false, ['page' => $page])->toArray();

        if (isset($list['data'])) {
            foreach ($list['data'] as &$v) {
                $v['images'] = Env::get('app.baseurl', 'http://ies-admin.zhuo-zhuo.com') . $v['images'];
                $v['create_date'] = date("Y-m-d",$v['update_time']);
                $v['keyword'] = explode(',',$v['keyword']);
            }
        }
        $data['list']  = $list['data'] ?? [];

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
    public static function search($search, $page = 1, $size = 10) {
        $page = ($page >= 1) ? $page : 1;
        if($size <= 0) {
            $size = 10;
        }

        $fid = FactorService::innermost($search['fid']);
        if ($fid) {
            $where['fa_items_factor.factor_id'] = ['in', $fid];
        }
        $where['i.status'] = 1;
        if ($search['keyword']) {
            $where['name'] = ['like', "%{$search['keyword']}%"];
        }

        if ($search['uid']) {
            $where['user_id'] = ['=', $search['uid']];
        }

        $order  = 'i.id desc';
        $fields = 'i.*';
        $list   = Items::alias('i')->join('fa_items_factor', 'fa_items_factor.item_id = i.id', 'left')
            ->where($where)->field($fields)->orderRaw($order)->group('i.id')->paginate($size, false, ['page' => $page])->toArray();

        if (isset($list['data'])) {
            foreach ($list['data'] as &$v) {
                $v['images'] = Env::get('app.baseurl', 'http://ies-admin.zhuo-zhuo.com') . $v['images'];
                $v['create_date'] = date("Y-m-d",$v['update_time']);
                $v['keyword'] = explode(',',$v['keyword']);
            }
        }

        $data['total'] = $list['total'] ?? 0;
        $data['pages'] = (int)ceil($data['total'] / $size);
        $data['list']  = $list['data'];
        return $data;
    }
}
