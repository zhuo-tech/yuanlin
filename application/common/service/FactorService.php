<?php

namespace app\common\service;


class FactorService {

    public static function handle($factorId, $param) {
        $factorData = ['method' => 'disturbanceDegreeEcosystem'];
        $function   = $factorData['method'] ?? '';
        $sysOption  = [];
        if (empty($function)) {
            return [];
        }
        
        return static::$function($param, $sysOption);
    }

    /**
     * @brief 生态系统受干扰程度
     * @factor (|b1-b2|+|c1-c2|+|d1-d2|+|e1-e2|)/2*(b1+c1+d1+e1)×100%
     * @param $param
     * @param $sysOption
     */
    public static function disturbanceDegreeEcosystem($param, $sysOption) {
        extract($param);
        $data = (abs($b1 - $b2) + abs($c1 - $c2) + abs($d1 - $d2) + abs($e1 - $e2)) / ($sysOption[0] * ($b1 + $c1 + $d1 + $e1));
        return $data * $sysOption[1];
    }
}
