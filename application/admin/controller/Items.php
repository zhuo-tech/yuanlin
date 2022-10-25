<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use fast\Tree;
use think\Db;
use think\exception\DbException;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\response\Json;
use app\admin\model\City as cityModel;

/**
 * 
 *
 * @icon fa fa-circle-o
 */
class Items extends Backend
{

    /**
     * Items模型对象
     * @var \app\admin\model\Items
     */
    protected $model = null;

    protected $itemType = null;

    protected $catelist = [];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Items;

        $this->itemType = ['1'=>'城市绿化','2'=>'乡村生态'];

        $this->view->assign('itemType', $this->itemType);



        $dataList = \think\Db::name("items_cate")->field('*', true)->order('id ASC')->select();
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

        $query = $this->model->where($where);
        $list = $query
            ->order($sort, $order)
            ->paginate($limit);

        $total = $list->total();
        $rows = $list->items();

        foreach ($rows as &$row){
            $cate = \app\admin\model\ItemsCate::where(['id'=>$row['item_cate_id']])->find();
            $row['item_cate_name'] = $cate['name'];
            //$row['item_type'] = $this->itemType[$row['item_type']];
        }

        $result = ['total' => $total, 'rows' =>$rows];
        return json($result);
    }



    /**
     * 编辑
     *
     * @param $ids
     * @return string
     * @throws DbException
     * @throws \think\Exception
     */
    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds) && !in_array($row[$this->dataLimitField], $adminIds)) {
            $this->error(__('You have no permission'));
        }
        if (false === $this->request->isPost()) {
            $this->view->assign('row', $row);
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);
        $result = false;
        Db::startTrans();
        try {
            //是否采用模型验证
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                $row->validateFailException()->validate($validate);
            }

            $params = $this->handle($params);


            $result = $row->allowField(true)->save($params);
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if (false === $result) {
            $this->error(__('No rows were updated'));
        }
        $this->success();
    }


    public function handle($param){

        $locations = explode('/',$param['location']);
        $province = cityModel::where('area_name','like',$locations[0])->where(['level'=>1])->select()->toArray();
        $param['province'] = $province['area_code'];

        $city = cityModel::get(['area_name'=>$locations[1]])->toArray();
        $param['city'] = $city['area_code'];
        $region = cityModel::get(['area_name'=>$locations[2]])->toArray();
        $param['area'] = $region['area_code'];

        return $param;

    }


}
