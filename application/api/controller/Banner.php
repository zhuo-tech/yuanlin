<?php

namespace app\api\controller;

use app\admin\model\News as NewsModel;
use app\common\controller\Api;
use app\admin\model\Banner as BannerModel;
use app\admin\model\Factor as FactorModel;
use app\common\service\ImagesService;
use think\Db;
use think\Env;
use think\Request;

/**
 * Banner接口
 */
class Banner extends Api {
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    protected $data = [];

    /**
     * @brief Banner
     */
    public function index() {
        $banners = BannerModel::where(['status' => 1])
            ->where('type','<','3')
            ->limit(3)
            ->field(['id', 'name', 'image', 'type', 'link','content','item_id'])
            ->order('sort', 'asc')->select()->toArray();
        foreach ($banners as &$banner){
            $banner['image'] = ImagesService::getBaseUrl().$banner['image'];
        }
        $news= NewsModel::field(['id', 'name', 'image', 'type', 'link','create_time'])
            ->where(['status' => 1])
            ->limit(5)
            ->order('sorts', 'asc')->select()->toArray();

        foreach ($news as &$new){
            $new['image'] = ImagesService::getBaseUrl().$new['image'];
            $new['create_date'] = date("Y-m-d",$new['create_time']);
        }
        $total = FactorModel::where(['status'=>1])->count();
        $first = FactorModel::where(['pid'=>0,'status'=>1])->count();
        $second = FactorModel::alias('f1')
            ->join('factor f2','f1.pid=f2.id','left')
            ->where(['f2.pid'=>0,'f2.status'=>1])
            ->field('f1.id')->count();
        $third = $total-$first-$second;

        return json(['code' => 0, 'message' => 'OK', 'banner' => $banners,'news'=>$news,'module'=>$first,'second'=>$second,'third'=>$third]);
    }

    public function test(){

        $array = [
            [
                'id' => 134,
                'breakId' => 0
            ],
            [
                'id' => 135,
                'breakId' => 0
            ],
            [
                'id' => 136,
                'breakId' => 0
            ],
            [
                'id' => 137,
                'breakId' => 135
            ],
            [
                'id' => 138,
                'breakId' => 137
            ],

        ];

        $index = array_column($array, 'id');
        foreach ($array as $key => &$val) {
            foreach ($array as $k=>$v){
                if($v['breakId']==$val['id']){
                    $val['child'][]= $v;
                }
            }
        }
        foreach ($array as $n=>&$m){
            $this->pushData($m);
            if(isset($m['child'])){
                $m['child'] =$this->getChild($m['child'],$array,$index);
            }
        }
        dump($array);
        dump($this->data);
    }

    public function getChild($node,$total,$indexs){
        $this->pushData($node[0]);
        $ind= array_search($node[0]['id'],$indexs);
        if(isset($total[$ind]['child'])){
            $node[0]['child'] = $this->getChild($total[$ind]['child'],$total,$indexs);
        }
        return $node;
    }
    public function pushData($data){

        $indexs = array_column($this->data, 'id');

        if(!array_search($data['id'],$indexs)){
            array_push($this->data,['id'=>$data['id']]);
        };
    }


    public function zhiye(Request $request){

        $id = $request->param('id','');

        $profession = ['科研院所','高等学校','规划设计院','其他'];
        $detail = [
            ['研究员','副研究员','助理研究员','研究实习员'],
            ['教授','副教授','讲师','研究生','本科生'],
            ['正高级工程师','副高级工程师','项目负责人','设计师','设计师助理'],
            ['其他']
            ];

        if($id){
            $data =Db::table('fa_profession')
                ->where(['pid'=>$id])
                ->field("*")
                ->select()->toArray();
        }else{
            $data =Db::table('fa_profession')
                ->where(['pid'=>0])
                ->field("*")
                ->select()->toArray();
        }



        return json(['code' => 0, 'message' => 'OK', 'data'=>$data]);

    }

    public function addBanner(Request $request){

        $type  = $request->param('type');

        $banners = BannerModel::where(['status' => 1])
            ->where('type','>','2')
            ->field(['id', 'name','name2', 'image', 'type', 'link','content'])
            ->order('type', 'asc')->select()->toArray();
        foreach ($banners as &$banner){
            $banner['image'] = ImagesService::getBaseUrl().$banner['image'];
        }

        return json(['code' => 0, 'message' => 'OK', 'banner' => $banners]);

    }
}
