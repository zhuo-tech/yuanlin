<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\service\DownloadService;
use app\common\service\FactorService;
use app\common\service\ItemFactorService;
use think\Request;

/**
 * @title 下载中心
 * @description 接口说明
 */
class Download extends Api {
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * @brief 案例分类
     */
    public function type() {
        $data = DownloadService::type();
        return json(['code' => 0, 'data' => $data, 'message' => 'OK']);
    }

    /**
     *
     * @brief 下载案例
     */
    public function cases(Request $request) {
        $page = $request->param('page', 0);
        $type = $request->param('type');
        $name = $request->param('keyword');

        $data = DownloadService::search(['type' => $type, 'keyword' => $name], $page, ['id', 'name', 'year', 'description']);
        return json(['code' => 0, 'data' => $data, 'message' => 'OK']);
    }
}
