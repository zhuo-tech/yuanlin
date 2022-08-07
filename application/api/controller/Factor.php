<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\service\FactorService;
use app\common\service\ItemFactorService;
use think\Request;

/**
 * 首页接口
 */
class Factor extends Api {
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * @brief 获取所有项目指标
     */
    public function tree() {
        $data = FactorService::getFactorTree();
        return json(['code' => 0, 'data' => $data, 'message' => 'OK']);
    }

    /**
     * @brief  保存指标
     * $itemId  = 1;
     * $factors = [21, 41, 22, 23];
     */
    public function saveFactors(Request $request) {
        $itemId  = $request->param('item_id', 0);
        $factors = $request->param('factors', []);


        $data = ItemFactorService::saveFactors((int)$itemId, $factors);
        return json(['code' => $data['error'], 'data' => [], 'message' => $data['message']]);
    }

    /**
     * @brief 确认指标
     */
    public function confirm(Request $request) {
        $itemId = $request->param('item_id', 0);
        $data   = ItemFactorService::doSure((int)$itemId);
        return json(['code' => $data['error'], 'data' => [], 'message' => $data['message']]);
    }

    /**
     * @brief 执行计算指标
     * item_id = 1,
     * factors => [
     *         [
     *             'id'    => 1,
     *             'param' => ['b1' => 1, 'c1' => 2]
     *         ],
     *         [
     *             'id'    => 2,
     *             'param' => []
     *         ],
     *     ]
     */
    public function execute(Request $request) {
        $itemId  = $request->param('item_id', 0);
        $factors = $request->param('factors', []);

//        $itemId  = 1;
//        $factors = [
//            [
//                'id'    => 21,
//                'param' => [
//                    'b1' => 1,
//                    'b2' => 2,
//                    'c1' => 3,
//                    'c2' => 4,
//                    'd1' => 5,
//                    'd2' => 6,
//                    'e1' => 7,
//                    'e2' => 8
//                ]
//            ],
//            [
//                'id'    => 41,
//                'param' => []
//            ],
//            [
//                'id' => 23,
//                'param' => [
//                    'b' => 10000,
//                    'c' => 1233
//                ]
//            ]
//        ];
        $data    = ItemFactorService::executeFactors((int)$itemId, $factors);
        return json(['code' => $data['error'], 'data' => [], 'message' => $data['message']]);
    }

    /**
     * 获取保存的指标
     */
    public static function getSaveFactors(Request $request) {
        $itemId = $request->param('item_id', 0);
        $data   = FactorService::getFactorTree($itemId);
        return json(['code' => 0, 'data' => $data, 'message' => 'OK']);
    }
}
