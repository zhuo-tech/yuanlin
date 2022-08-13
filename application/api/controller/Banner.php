<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\admin\model\Banner as BannerModel;

/**
 * Banner接口
 */
class Banner extends Api {
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     *@brief Banner
     */
    public function index() {
        $banners = BannerModel::where(['status' => 1])->field(['id', 'name', 'image', 'type', 'link'])->order('sort', 'asc')->select()->toArray();
        return json(['code' => 0, 'message' => 'OK', 'data'=> $banners]);
    }
}
