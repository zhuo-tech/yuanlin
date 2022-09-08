<?php

namespace app\api\controller;

use app\admin\model\News as NewsModel;
use app\common\controller\Api;
use app\admin\model\Banner as BannerModel;
use app\admin\model\Factor as FactorModel;

/**
 * Banner接口
 */
class Banner extends Api {
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * @brief Banner
     */
    public function index() {
        $banners = BannerModel::where(['status' => 1])->field(['id', 'name', 'image', 'type', 'link'])
            ->order('sort', 'asc')->select()->toArray();
        $news= NewsModel::field(['id', 'name', 'image', 'type', 'link'])
            ->order('sorts', 'asc')->select()->toArray();
        $total = FactorModel::where(['status'=>1])->count();
        $first = FactorModel::where(['pid'=>0,'status'=>1])->count();
        $second = FactorModel::alias('f1')
            ->join('factor f2','f1.pid=f2.id','left')
            ->where(['f2.pid'=>0,'f2.status'=>1])
            ->field('f1.id')->count();
        $third = $total-$first-$second;

        return json(['code' => 0, 'message' => 'OK', 'banner' => $banners,'news'=>$news,'module'=>$first,'second'=>$second,'third'=>$third]);
    }
}
