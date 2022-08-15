<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\model\Config as ConfigModel;
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
        $config['cdn_url'] = $dbConfig['cdnurl'];
        $config['site']    = json_decode($dbConfig['siteConfig'], true);
        dump($config);die();
    }
}
