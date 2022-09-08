<?php
namespace app\api\controller;

use app\common\controller\Api;
use app\admin\model\News as NewsModel;

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
}
