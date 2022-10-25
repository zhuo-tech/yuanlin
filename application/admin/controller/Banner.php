<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 
 *
 * @icon fa fa-circle-o
 */
class Banner extends Backend
{

    /**
     * Banner模型对象
     * @var \app\admin\model\Banner
     */
    protected $model = null;

    protected $bannerType = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Banner;

        $this->bannerType = ['1'=>'指标','2'=>'案例','3'=>'新建城市','4'=>'新建乡村'];

        $this->view->assign("bannerType", $this->bannerType);
    }



    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


}
