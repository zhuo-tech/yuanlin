<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\model\Config as ConfigModel;
use think\Env;
use think\Request;

/**
 * @title 下载中心
 * @description 接口说明
 */
class Config extends Api {
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * @brief 案例分类
     */
    public function config() {
        $dbConfig = ConfigModel::field(['name', 'content'])->column('value', 'name');
        $config   = [];
        // 配置数据
        $config['name']    = $dbConfig['name'];
        $config['icp']     = $dbConfig['beian'];
        $config['cdn_url'] = Env::get('app.baseurl', 'http://ies-admin.zhuo-zhuo.com');
        $config['site']    = json_decode($dbConfig['siteConfig'], true);
        return json(['code' => 0, 'data' => $config, 'message' => 'OK']);
    }
}
