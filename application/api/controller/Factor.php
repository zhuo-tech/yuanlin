<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\service\FactorService;
use app\common\service\ItemFactorService;
use think\Request;

/**
 * 指标接口
 */
class Factor extends Api {
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * @@ApiTitle (获取所有项目指标)
     *
     *@ApiReturn   (name="data", type="object", sample="[{'name':'string','id':'int','child':[{'email':'string','age':'integer'}]}]", description="扩展数据返回")
     */
    public function tree() {
        $data = FactorService::getFactorTree();
        return json(['code' => 0, 'data' => $data, 'message' => 'OK']);
    }

    /**
     * @ApiTitle  (保存指标)
     * $itemId  = 1;
     * $factors = [21, 41, 22, 23];
     * @ApiParams   (name="item_id", type="integer", required=true, description="项目id")
     * @ApiParams   (name="factors", type="array", required=true,sample="[21, 41, 22, 23]", description="指标id")

     * @ApiReturn   ({
        'code':'0',
        'mesg':'返回成功'
        })
     *
     */
    public function saveFactors(Request $request) {
        $itemId  = $request->param('item_id');
        $factors = $request->param('factors/a');

        $data = ItemFactorService::saveFactors((int)$itemId, $factors);
        return json(['code' => $data['error'], 'data' => [], 'message' => $data['message']]);
    }

    /**
     * @ApiTitle (确认指标)
     *
     * @ApiParams   (name="item_id", type="integer", required=true, description="项目id")
     * @ApiReturn   ({
            'code':'0',
            'mesg':'返回成功'
            })
     *
     */
    public function confirm(Request $request) {
        $itemId = $request->param('item_id', 0);
        $data   = ItemFactorService::doSure((int)$itemId);
        return json(['code' => $data['error'], 'data' => [], 'message' => $data['message']]);
    }

    /**
     * @ApiTitle (执行计算指标)
     * @ApiParams   (name="item_id", type="integer", required=true, description="项目id")
     * @ApiParams   (name="factors", type="object", required=true, sample="[{'id':1,'param':{'b1':1,'c1':2}},{'id':2,'param':{'b1':1}}]", description="指标id")

     * @ApiReturn   ({
        'code':'0',
        'mesg':'返回成功'
        })
     *
     */
    public function execute(Request $request) {
        $itemId  = $request->param('item_id', 0);
        $factors = $request->param('factors', []);

        $data    = ItemFactorService::executeFactors((int)$itemId, $factors);
        return json(['code' => $data['error'], 'data' => [], 'message' => $data['message']]);
    }

    /**
     * @ApiTitle (获取保存的指标)
     *
     *@ApiReturnParams   (name="data", type="string", sample="name", description="名称")
     *@ApiReturnParams   (name="id", type="integer", sample="1", description="ID")
     *@ApiReturnParams   (name="input_mode", type="string", sample="A", description="输入模式")
     *@ApiReturnParams   (name="option", type="object", sample="[{'var':'b','name':'河湖水系自然岸线长度（km）','tip':''}]", description="配置选项")
     *@ApiReturnParams   (name="coefficient", type="array", sample="[100]", description="系数")
     *@ApiReturnParams   (name="method", type="string", sample="greenVisionRate", description="执行函数")
     *@ApiReturnParams   (name="meaning", type="string", sample="重点保护生物指数；", description="含义")
     *@ApiReturnParams   (name="calc_method", type="string", sample="42.2*a+(-73*b)+(-57.8*c)+(-2.1*d)+(429.70*e)+(-25.2*f)+(-0.0005*g)/h", description="计算方式")
     *@ApiReturnParams   (name="source", type="string", sample="XX", description="数据来源")
     */
    public static function getSaveFactors(Request $request) {
        $itemId = $request->param('item_id', 0);
        $data   = FactorService::getFactorTree($itemId);
        return json(['code' => 0, 'data' => $data, 'message' => 'OK']);
    }
}
