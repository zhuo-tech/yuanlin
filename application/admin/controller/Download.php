<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use fast\Tree;
use think\Db;
use think\exception\DbException;
use think\response\Json;

/**
 * 
 *
 * @icon fa fa-download
 */
class Download extends Backend
{

    /**
     * Download模型对象
     * @var \app\admin\model\Download
     */
    protected $model = null;
    protected $catelist = [];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Download;


        $dataList = \think\Db::name("download_cate")->field('*', true)->order('id ASC')->select();
        foreach ($dataList as $k => &$v) {
            $v['name'] = __($v['name']);
        }
        unset($v);
        Tree::instance()->init($dataList);

        $this->catelist = Tree::instance()->getTreeList(Tree::instance()->getTreeArray(0), 'name');

        $catedata = [0 => __('None')];

        foreach ($this->catelist as $k => $v) {
            $catedata[$v['id']] = $v['name'];
        }
        $this->view->assign('catedata', $catedata);

    }



    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */



    /**
     * 查看
     *
     * @return string|Json
     * @throws \think\Exception
     * @throws DbException
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if (false === $this->request->isAjax()) {
            return $this->view->fetch();
        }
        //如果发送的来源是 Selectpage，则转发到 Selectpage
        if ($this->request->request('keyField')) {

            $res = $this->selectpage();
            return $res;
        }
        [$where, $sort, $order, $offset, $limit] = $this->buildparams();

//        $query = $this->model->where($where);
//        $list = $query
//            ->order($sort, $order)
//            ->paginate($limit);

        $filter = $this->request->get("filter", '');

        $filter = \GuzzleHttp\json_decode($filter,1);

        if(isset($filter['download_cate_name'])){
            $filter['dc.name'] = $filter['download_cate_name'];
            unset($filter['download_cate_name']);
        }

        if(isset($filter['name'])){
            $filter['d.name'] = $filter['name'];
            unset($filter['name']);
        }

        $total = Db::table('fa_download d')
            ->join('fa_download_cate dc','d.download_cate_id=dc.id','left')
            ->where($filter)
            ->count();
        $rows = Db::table('fa_download d')
            ->field("d.*,dc.name as download_cate_name")
            ->join('fa_download_cate dc','d.download_cate_id=dc.id','left')
            ->where($filter)
           ->limit($limit)->select()->toArray();

//        $total = $list->total();
//        $rows = $list->items();

        $result = ['total' => $total, 'rows' =>$rows];
        return json($result);
    }

}
