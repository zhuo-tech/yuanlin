<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\service\FactorService;
use app\common\service\ItemCategoryService;
use app\common\service\ItemFactorService;
use app\common\service\ItemService;
use think\Request;

/**
 * 项目分类接口
 */
class Category extends Api {
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    public function category() {
        $data = ItemCategoryService::category();
        return json(['code' => 0, 'message' => 'OK', 'data' => $data]);
    }
}
