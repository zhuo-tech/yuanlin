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
     * 首页
     *
     *@ApiReturnParams   (name="data", type="object", sample="[{'name':'string','id':'int','child':[{'email':'string','age':'integer'}]}]", description="扩展数据返回")
     */
    public function index() {
        $banners = BannerModel::where(['status' => 1])->field(['id', 'name', 'image', 'type', 'link'])->order('sort', 'asc')->select()->toArray();
        return json(['code' => 0, 'message' => 'OK', 'data'=> $banners]);
    }
}
