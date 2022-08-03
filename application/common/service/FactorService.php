<?php

namespace app\common\service;


use app\admin\model\FactorDetail as FactorDetailModel;
use app\admin\model\ItemsFactor;
use think\exception\DbException;

class FactorService {
    // 用户输入参数
    public static $param = [];
    // 系统配置系数
    public static $coefficient = [];

    /**
     * @throws DbException
     */
    public static function handle($itemId, $factorId, $param): array {
        try {
            // 查询factor_detail
            $factorDetail = FactorDetailModel::get($factorId);
            // 获取item的factor参数
            $itemFactor = ItemsFactor::where(['item_id' => $itemId, 'factor_id' => $factorId])->select();
            if (empty($itemFactor)) {
                return [];
            }
            // 获取因子计算公式执行函数
            $function = $factorDetail['method'] ?? '';
            if (empty($function)) {
                return [];
            }

            // 获取公式的系统配置系数
            static::$coefficient = json_decode($factorDetail['coefficient'], true);
            // 获取公式的用户输入参数
            static::$param = json_decode($itemFactor['param'], true);

            return static::$function();
        } catch (\Exception $exception) {
            return [];
        }
    }

    /**
     * @brief 生态系统受干扰程度
     * @factor (|b1-b2|+|c1-c2|+|d1-d2|+|e1-e2|)/2*(b1+c1+d1+e1)×100%
     */
    public static function disturbanceDegreeEcosystem(): string {
        extract(static::$param);
        $coefficient = static::$coefficient;

        $data = (abs($b1 - $b2) + abs($c1 - $c2) + abs($d1 - $d2) + abs($e1 - $e2)) / ($coefficient[0] * ($b1 + $c1 + $d1 + $e1));
        $data = $data * $coefficient[1];

        return static::format($data / 100);
    }

    /**
     * @brief  单位面积水源涵养量
     * @factor [(b-c1-b×d1)×e1+(b-c2-b×d2)×e2+…(b-c5-b×d5)×e5]/(e1+e2+e3+e4+e5)×10-4
     */
    public static function waterConservationPerUnitArea() {
        extract(static::$param);
        $coefficient = static::$coefficient;

        return (($b - $c1 - $b * $d1) * $e1 + ($b - $c2 - $b * $d2) * $e2 + ($b - $c3 - $b * $d3) * $e3 + ($b - $c4 - $b * $d4) * $e4 + ($b - $c5 - $b * $d5) * $e5) / (($e1 + $e2 + $e3 + $e4 + $e5) * $coefficient[0]);
    }

    /**
     * @brief  自然岸线保有率
     * @factor b/c×100%
     */
    public static function naturalShorelineRetentionRate(): string {
        extract(static::$param);
        $coefficient = static::$coefficient;

        $data = ($b / $c) * $coefficient[0];
        return static::format($data / 100);
    }

    /**
     * @brief 单位面积树木雨水拦截量
     * @factor (b1×c1+b2×c2+..+bn×cn)/d
     * @return float|int
     */
    public static function rainwaterInterceptionTreesPerUnitArea() {
        /*
         * $array = [
            'd' => 10000,
            'trees' => [
                [
                    'b1' => 111,
                    'c1' => 222
                ],
            ],
        ];*/

        extract(static::$param);
        $sum = 0;
        foreach ($trees as $tree) {
            $sum += array_product($tree);
        }
        return $sum / $d;
    }

    /**
     * @brief 土壤保护
     * @factor b/c×100%
     */
    public static function soilProtection(): string {
        extract(static::$param);
        $coefficient = static::$coefficient;

        $data = $b * $c;
        return static::format($data / $coefficient[0]);
    }

    private static function format($number): string {
        return sprintf('%.2f', $number) . '%';
    }
}
