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
     *@ApiTitle (Banner)
     *
     *@ApiReturnParams   (name="id", type="string", sample="1", description="ID")
     *@ApiReturnParams   (name="name", type="string", sample="HHHH", description="名称")
     *@ApiReturnParams   (name="image", type="string", sample="/uploads/20220811/6323410f66781753660adeca5eb9b5f8.png", description="图片")
     *@ApiReturnParams   (name="type", type="string", sample="2", description="1：指标 2：案例")
     *@ApiReturnParams   (name="link", type="string", sample="z.com", description="链接")
     */
    public function index() {
        $banners = BannerModel::where(['status' => 1])->field(['id', 'name', 'image', 'type', 'link'])->order('sort', 'asc')->select()->toArray();
        return json(['code' => 0, 'message' => 'OK', 'data'=> $banners]);
    }
}
