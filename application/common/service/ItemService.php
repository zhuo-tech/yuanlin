<?php

namespace app\common\service;


use app\admin\model\ItemsFactor as ItemFactorModel;
use app\admin\validate\Items as ItemsValidate;
use app\admin\model\Items;
use app\admin\model\City as cityModel;
use think\Db;
use think\Env;
use function AlibabaCloud\Client\value;


/**
 * 项目
 */
class ItemService {

    /**
     * @brief 创建项目
     * @param $data
     * @return array
     */
    public static function saveItem($data, $uid) {
        $validate = new ItemsValidate();
        if (!$validate->scene('insert')->check($data)) {
            return ['error' => 1, 'message' => $validate->getError(), 'data' => []];
        }

        if (!$uid || (intval($uid) != $uid)) {
            return ['error' => 1, 'message' => '用户信息有误', 'data' => []];
        }

        $data['user_id'] = $uid;
        $data['location'] = self::getLocation($data);
        $images = ImagesService::handleInput($data['images']);
        $data['images'] = $images;
        $model  = new Items($data);
        $result = $model->allowField(true)->save();
        if ($result) {
            return ['error' => 0, 'message' => '创建成功', 'data' => ['id' => $model->id]];
        }
        return ['error' => 1, 'message' => '创建失败', 'data' => []];
    }

    public static function editItem($data){

        $model = Items::get(['id'=>$data['id']]);

        $data['location'] = self::getLocation($data);
        $model->name = $data['name'];
        $model->province = $data['province'];
        $model->city = $data['city'];
        $model->area = $data['area'];
        $model->location = $data['location'];
        $model->build_time = $data['build_time'];
        $model->designer_team = $data['designer_team'];
        $model->study_team = $data['study_team'];
        $model->introduction = $data['introduction'];

        $images = ImagesService::handleInput($data['images']);

        $model->images = $images;

        $result = $model->save();

        return ['error' => 0, 'message' => '修改成功', 'data' => ['id' => $model->id]];

//        if ($result) {
//            return ['error' => 0, 'message' => '修改成功', 'data' => ['id' => $model->id]];
//        }
//        return ['error' => 1, 'message' => '修改失败', 'data' => []];

    }

    public static function getLocation($data){
        $locations       = [];
        $province        = cityModel::get(['area_code' => $data['province']])->toArray();
        array_push($locations, $province['area_name']);
        $city = cityModel::get(['area_code' => $data['city']])->toArray();
        array_push($locations, $city['area_name']);
        $region = cityModel::get(['area_code' => $data['area']])->toArray();
        array_push($locations, $region['area_name']);
        $location = implode('/', $locations);

        return $location;

    }

    /**
     * @分类项目列表
     * @param $search
     * @param $page
     * @param $fields
     * @return array
     */
    public static function cate($search, $page = 1, $fields = '*') {
        $page   = ($page >= 1) ? $page : 1;
        $filter = '';
        $sort   = [
            'area'     => [
                1 => 'areas < 10',
                2 => 'areas >= 10 and areas <50',
                3 => 'areas >= 50 and areas <100',
                4 => 'areas >= 100 and areas <500',
                5 => 'areas >= 500 and areas <1000',
                6 => 'areas >1000',
            ],
            'contoury' => [
                1 => 'build_time < 2000',
                2 => 'build_time >= 2000 and build_time < 2010',
                3 => 'build_time >= 2010 and build_time < 2020',
                4 => 'build_time >= 2020',
            ]
        ];

        $cidRow = ItemCategoryService::innermost($search['cid']);
        $cid    = array_column($cidRow, 'id');
        $keys   = array_column($cidRow, 'type');
        $key    = current($keys);
        if ($cid) {
            if ($key) {
                // 获取过滤条件
                if (count($cidRow) == 1) {
                    $row  = current($cidRow);
                    $type = $row['sorts'] ?? 0;
                    if ($type) {
                        $filter = $sort[$key][$type] ?? '';
                    }
                } else {
                    $where['item_cate_id'] = ['in', $cid];
                }
            } else {
                $where['item_cate_id'] = ['in', $cid];
            }
        }

        $where['status'] =['>',0];
        if ($search['keyword']) {
            $where['name'] = ['like', "%{$search['keyword']}%"];
        }

        if ($search['uid']) {
            $where['user_id'] = ['=', $search['uid']];
        }

        $order = 'id desc';
        $limit = 10;
        $list  = Items::where($where);
        if ($filter) {
            $list = $list->where($filter);
        }
        $list = $list->field($fields)->orderRaw($order)->paginate($limit, false, ['page' => $page])->toArray();
        if (isset($list['data'])) {
            foreach ($list['data'] as &$v) {
                $images = explode(',',$v['images']);
                $v['images']      =  ImagesService::getBaseUrl() . $images[0];
                $v['create_date'] = date("Y-m-d", $v['update_time']);
                $v['keyword']     = explode(',', $v['keyword']);
            }
        }
        $data['list'] = $list['data'] ?? [];

        $data['total'] = $list['total'];
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
        if ($size <= 0) {
            $size = 10;
        }

        $fid = FactorService::innermost($search['fid']);
        if ($fid) {
            $where['fa_items_factor.factor_id'] = ['in', $fid];
        }
        $where['status'] =['>',0];
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
                $images = explode(',',$v['images']);
                $v['images']      =  ImagesService::getBaseUrl() . $images[0];
                $v['create_date'] = date("Y-m-d", $v['update_time']);
                $v['keyword']     = explode(',', $v['keyword']);
            }
        }

        $data['total'] = $list['total'] ?? 0;
        $data['pages'] = (int)ceil($data['total'] / $size);
        $data['list']  = $list['data'];
        return $data;
    }



    public static function getItemByFactorId($factorId){

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
}
