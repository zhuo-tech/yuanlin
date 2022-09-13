<?php

namespace app\admin\validate;

use think\Validate;

class Items extends Validate {

    /**
     * 验证规则
     */
    protected $rule = [
        'name'          => 'require|max:200',
        'item_type'     => 'require|number|between:1,2',
        'province'      => ['require', 'regex' => '/^\d{6}$/'],
        'city'          => ['require', 'regex' => '/^\d{6}$/'],
        'area'          => ['require', 'regex' => '/^\d{6}$/'],
        'areas'         => ['require', 'regex' => '/^[0-9]+(.[0-9]+)?$/'],
        'item_cate_id'  => ['require', 'regex' => '/^\+?[1-9][0-9]*$/'],
        'images'        => ['require'],
        'designer_team' => ['require'],
        'introduction'  => ['require'],
    ];

    protected $message = [
        'name.require'          => '项目名称必填',
        'title.max'             => '项目名称最多不能超过200个字符',
        'item_type.require'     => '项目类型必填',
        'item_type.number'      => '项目类型参数有误',
        'item_type.between'     => '项目类型选择有误',
        'province.require'      => '省份必选',
        'province.regex'        => '省份必须为6位数字',
        'city.require'          => '市必选',
        'city.regex'            => '市必须为6位数字',
        'area.require'          => '区县必选',
        'area.regex'            => '区县必须为6位数字',
        'areas.require'         => '面积必填',
        'areas.regex'           => '面积必须为数字',
//        'item_cate_id.require'  => '项目分类必填',
//        'item_cate_id.regex'    => '项目分类有误',
        'images.require'        => '图片必传',
        'introduction.require'  => '项目描述必填',
        'designer_team.require' => '设计团队必填'
    ];


    /**
     * 验证场景
     */
    protected $scene = [
        'insert' => ['name', 'item_type', 'province', 'city', 'area', 'areas', 'item_cate_id', 'images', 'designer_team', 'introduction']
    ];

}
