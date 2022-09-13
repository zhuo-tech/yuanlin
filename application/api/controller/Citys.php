<?php

use app\admin\model\City as cityModel;
use app\common\controller\Api;

class Citys extends Api {

    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    public function provinces(){

        $news= cityModel::field(['id', 'name'])
            ->where(['pid'=>-1])
            ->order('id', 'asc')->select()->toArray();
        return json(['code' => 0, 'message' => 'OK', 'data' => $news]);

    }

    public function cityList(){

    }
}
