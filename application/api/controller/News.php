<?php
namespace app\api\controller;

use app\common\controller\Api;
use app\admin\model\News as NewsModel;
use app\admin\model\City as cityModel;
use think\Request;

/**
 * Banner接口
 */
class News extends Api {
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * @brief Banner
     */
    public function index() {
        $news= NewsModel::field(['id', 'name', 'image', 'type', 'link'])
            ->order('sorts', 'asc')->select()->toArray();
        return json(['code' => 0, 'message' => 'OK', 'data' => $news]);
    }


    public function provinces(){

        $province= cityModel::field(['id', 'area_name','area_code'])
            ->where(['pid'=>-1])
            ->order('id', 'asc')->select()->toArray();
        return json(['code' => 0, 'message' => 'OK', 'data' => $province]);

    }


    public function cityList(Request $request){


        $pid   = $request->param('pid');

        $citys= cityModel::field(['id', 'area_name','area_code'])
            ->where(['pid'=>$pid])
            ->order('id', 'asc')->select()->toArray();
        return json(['code' => 0, 'message' => 'OK', 'data' => $citys]);

    }
}
