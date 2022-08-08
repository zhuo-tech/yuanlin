<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\exception\DbException;
use think\response\Json;

/**
 * 
 *
 * @icon fa fa-circle-o
 */
class ItemsFactor extends Backend
{

    /**
     * ItemsFactor模型对象
     * @var \app\admin\model\ItemsFactor
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\ItemsFactor;

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

        $query = $this->model->where($where);
        $list = $query
            ->order($sort, $order)
            ->paginate($limit);

        $total = $list->total();
        $rows = $list->items();

        foreach ($rows as &$row){
            $factor = \app\admin\model\Factor::where(['id'=>$row['factor_id']])->find();
            $row['factor_name'] = $factor['name'];

            $item = \app\admin\model\Items::where(['id'=>$row['item_id']])->find();
            $row['item_name'] = $item['name'];

        }

        $result = ['total' => $total, 'rows' =>$rows];
        return json($result);
    }



}
