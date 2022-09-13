<?php

namespace app\common\service;


use app\admin\model\Download as DownloadModel;
use app\admin\model\DownloadCate;
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
        if ($type) {
            $where['download_cate_id'] = $type;
        }
        $where['status'] = 1;
        if ($search['keyword']) {
            $where['name'] = ['like', "%{$search['keyword']}%"];
        }

        $order         = 'id desc';
        $limit         = 10;
        $list          = DownloadModel::where($where)->field($fields)->paginate($limit, false, ['page' => $page])->toArray();
        if (isset($list['data'])) {
            foreach ($list['data'] as &$v) {
                $v['image'] = Env::get('app.baseurl', 'http://ies-admin.zhuo-zhuo.com') . $v['image'];
            }
        }
        $data['total'] = $list['total'];
        $data['pages'] = (int)ceil($list['total'] / $limit);
        $data['list']  = $list['data'];
        return $data;
    }
}
