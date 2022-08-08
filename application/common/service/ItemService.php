<?php

namespace app\common\service;


use app\admin\validate\Items as ItemsValidate;
use app\admin\model\Items;
use function GuzzleHttp\Psr7\str;


/**
 * 项目
 */
class ItemService {

    /**
     * @brief 创建项目
     * @param $data
     * @return array
     */
    public static function saveItem($data) {
        $validate = new ItemsValidate();
        if (!$validate->scene('insert')->check($data)) {
            return ['error' => 1, 'message' => $validate->getError(), 'data' => []];
        }

        $model  = new Items($data);
        $result = $model->allowField(true)->save();
        if ($result) {
            return ['error' => 0, 'message' => '创建成功', 'data' => []];
        }
        return ['error' => 1, 'message' => '创建失败', 'data' => []];
    }
}
