<?php

namespace app\common\service;


use app\admin\model\Download as DownloadModel;
use app\admin\model\DownloadCate;
use think\Db;
use think\Env;


/**
 * 项目
 */
class DownloadService {

    /**
     * @brief 获取案例分类
     */
    public static function type() {
        $rows = DownloadCate::field(['id', 'pid', 'name'])->select()->toArray();
        $data = [];
        foreach ($rows as $row) {
            if ($row['pid'] == 0) {
                $row['child'] = [];
                $data[]       = $row;
            }
        }

        $pids = array_column($data, 'id');
        foreach ($pids as $key => $pid) {
            foreach ($rows as $row) {
                if ($pid == $row['pid']) {
                    $data[$key]['child'][] = $row;
                }
            }
        }
        return $data;
    }

    public static function search($search, $page = 1, $fields = '') {
        $page = ($page >= 1) ? $page : 1;
        $type = $search['type'] ?? 0;
        $find = '';
        if ($type) {
//            $where['download_cate_id'] = $type;
            //$where[] = ['exp',Db::raw("FIND_IN_SET($type,download_cate_id)")];
            $typeArray = explode(',', $type);
            if ($typeArray) {
                $count = count($typeArray);
                $and   = ' and ';
                foreach ($typeArray as $key => $value) {
                    if($key + 1 >= $count) {
                        $and = '';
                    }
                    $find .= ' find_in_set(' . $value .', download_cate_id)' . $and;
                }
            }
        }
        $where['status'] = 1;
        if ($search['keyword']) {
            $where['name'] = ['like', "%{$search['keyword']}%"];
        }

        $order         = 'id desc';
        $limit         = 10;
        $model = new DownloadModel();
        $list          = DownloadModel::where($where);
        if($find) {
            $list = $list->where($find);
        }
        $list = $list->field($fields)->paginate($limit, false, ['page' => $page])->toArray();

        //echo $model->getLastSql();die;
        if (isset($list['data'])) {
            foreach ($list['data'] as &$v) {
                $v['image'] =  ImagesService::getBaseUrl() . $v['image'];
                $v['document'] = $v['document']?json_decode($v['document']):[];
            }
        }
        $data['total'] = $list['total'];
        $data['pages'] = (int)ceil($list['total'] / $limit);
        $data['list']  = $list['data'];
        return $data;
    }
}
