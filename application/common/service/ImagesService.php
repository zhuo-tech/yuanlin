<?php
namespace app\common\service;
use think\Env;

class ImagesService{

    public static function getBaseUrl(){
       $baseUrl =  Env::get("app.baseurl",'http://ies-admin.zhuo-zhuo.com');
       return $baseUrl;
    }

    public static function handleInput($images){
        $baseUrl = ImagesService::getBaseUrl();
        if(is_array($images)){
            $imgs = [];
            foreach ($images as $v){
                if(stristr($v,$baseUrl)){
                    $result = str_replace($baseUrl,'',$v);
                    array_push($imgs,$result);
                }
            }
            $images =  implode(',',$imgs);
        }else{
            if(stristr($images,$baseUrl)){
                $images = str_replace($baseUrl,'',$images);
            }
        }
        return $images;
    }

    public static function handleOut($images){

    }

}