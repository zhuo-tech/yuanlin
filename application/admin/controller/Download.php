<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use fast\Tree;
use think\Db;
use think\exception\DbException;
use think\exception\PDOException;
use think\exception\ValidateException;
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

        $string = urldecode(file_get_contents("php://input"));

        $params = $this->handle($params,$string);

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

    public function handle($param,$string){
        $arrays = explode('&',$string);
        $cate = [];
        foreach ($arrays as $array){
            $a = explode('=',$array);
            if($a[0]=='row[download_cate_id]'){
                array_push($cate,$a[1]);
            }
        }
        $param['download_cate_id'] = implode(',',$cate);
        return $param;
    }

}
