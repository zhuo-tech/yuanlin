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
 * @icon fa fa-circle-o
 */
class FactorDetail extends Backend
{

    /**
     * FactorDetail模型对象
     * @var \app\admin\model\FactorDetail
     */
    protected $model = null;
    protected $catelist = [];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\FactorDetail;

        $mode = ['A'=>'根据公式','C'=>'问卷模式','D'=>'直接输入结果'];

        $this->view->assign('mode', $mode);

        $dataList = \think\Db::name("factor")->field('*', true)->order('id ASC')->select();
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


        $filter = $this->request->get("filter", '');

        $filter = \GuzzleHttp\json_decode($filter,1);

        if(isset($filter['factor_name'])){
            $filter['f.name'] = ['like',"%{$filter['factor_name']}%"];
            unset($filter['factor_name']);
        }

//        var_dump($filter);die;

//        $query = $this->model->where($where);
//        $list = $query
//            ->order($sort, $order)
//            ->paginate($limit);
//
//        $total = $list->total();
//        $rows = $list->items();

        $total = Db::table('fa_factor_detail fd')
            ->join('fa_factor f','f.id=fd.factor_id','left')
            ->where($filter)->count();
        $rows = Db::table('fa_factor_detail fd')
            ->field("fd.*,f.name as factor_name")
            ->join('fa_factor f','f.id=fd.factor_id','left')
            ->order($sort, $order)
            ->where($filter)
            ->limit($offset,$limit)->select()->toArray();

        foreach ($rows as &$row){
//            $factor = \app\admin\model\Factor::where(['id'=>$row['factor_id']])->find();
//            $row['factor_name'] = $factor['name'];
        }

        $result = ['total' => $total, 'rows' =>$rows];
        return json($result);
    }

}
