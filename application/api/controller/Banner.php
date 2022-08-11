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
     *@ApiReturnParams   (name="option", type="object", sample="[{'var':'b','name':'河湖水系自然岸线长度（km）','tip':''}]", description="配置选项")
     *@ApiReturnParams   (name="coefficient", type="array", sample="[100]", description="系数")
     *@ApiReturnParams   (name="method", type="string", sample="greenVisionRate", description="执行函数")
     *@ApiReturnParams   (name="meaning", type="string", sample="重点保护生物指数；", description="含义")
     */
    public function index() {
        $banners = BannerModel::where(['status' => 1])->field(['id', 'name', 'image', 'type', 'link'])->order('sort', 'asc')->select()->toArray();
        return json(['code' => 0, 'message' => 'OK', 'data'=> $banners]);
    }
}
