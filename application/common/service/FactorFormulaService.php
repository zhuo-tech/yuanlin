<?php

namespace app\common\service;


use app\admin\model\FactorDetail as FactorDetailModel;
use app\admin\model\ItemsFactor;
use think\exception\DbException;


/**
 * 指数公式
 */
class FactorFormulaService {
    // 用户输入参数
    public static $param = [];
    // 系统配置系数
    public static $coefficient = [];

    /**
     * @throws DbException
     */
    public static function handle($itemId, $factorId, $param) {
        try {
            // 查询factor_detail
            $factorDetail = FactorDetailModel::where(['factor_id' => $factorId])->find()->toArray();
            // 获取因子计算公式执行函数
            $function = $factorDetail['method'] ?? '';
            if (empty($function)) {
                return [];
            }

            // 获取公式的系统配置系数
            static::$coefficient = json_decode($factorDetail['coefficient'], true);
            // 获取公式的用户输入参数
            static::$param = $param;

            return static::$function();
        } catch (\Exception $exception) {
            var_dump($exception->getMessage());
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
        return static::format($data / ($coefficient[0] / 100));
    }

    /**
     * @brief 植被覆盖率
     * @factor b/c×100%
     */
    public static function vegetationCoverage(): string {
        extract(static::$param);
        $coefficient = static::$coefficient;

        $data = $b * $c;
        return static::format($data / ($coefficient[0] / 100));
    }

    /**
     * @brief 植物群落构成
     * @factor b/c×100%
     */
    public static function compositionPlantCommunity(): string {
        extract(static::$param);
        $coefficient = static::$coefficient;

        $data = $b * $c;
        return static::format($data / ($coefficient[0] / 100));
    }

    /**
     * @brief 水网密度
     * @factor 1005.4788*(a/b)
     */
    public static function waterNetworkDensity() {
        extract(static::$param);
        $coefficient = static::$coefficient;
        return $coefficient[0] * ($a / $b);
    }

    /**
     * @brief 水源涵养能力
     * @factor 526.7926*{0.45 * [0.1 *a+ 0.3 * b+0.6 * (c+ d)+ 0.35 * [0.6 * e+0.25 * f+ 0.15 * g]+ 0.20 * [0.6 *h+ 0.3 * i+ 0.1 *j]}/k
     */
    public static function waterConservationCapacity() {
        extract(static::$param);
        $ii = static::$coefficient;

        return $ii[0] * ($ii[1] * ($ii[2] * $a + $ii[3] * $b + $ii[4] * ($c + $d)) + $ii[5] * ($ii[6] * $e + $ii[7] * $f + $ii[8] * $g) + $ii[9] * ($ii[10] * $h + $ii[11] * $i + $ii[12] * $j)) / $k;
    }

    /**
     * @brief  绿地平均降温
     * @factor [（a1-b1）+（a1-b2）]/2
     */
    public static function averageCoolingGreenSpace() {
        extract(static::$param);
        $coefficient = static::$coefficient;
        return (($a1 - $b1) + ($a1 - $b2)) / $coefficient[0];
    }

    /**
     * @brief  空气质量优良天数比例
     * @factor a/365*100%
     */
    public static function proportionDayExcellentAirQuality(): string {
        extract(static::$param);
        $coefficient = static::$coefficient;

        $data = $a / $coefficient[0];
        return static::format($data / ($coefficient[1] / 100));
    }

    /**
     * @brief  碳排放强度
     * @factor 42.2*a+(-73*b)+(-57.8*c)+(-2.1*d)+(429.70*e)+(-25.2*f)+(-0.0005*g)/h
     */
    public static function carbonEmissionIntensity() {
        extract(static::$param);
        $coefficient = static::$coefficient;

        return $coefficient[0] * $a + ($coefficient[1] * $b) + ($coefficient[2] * $c) + ($coefficient[3] * $d) + ($coefficient[4] * $e) + ($coefficient[5] * $f) + ($coefficient[6] * $g) / $h;
    }

    /**
     * @brief  生物量密度
     * @factor (6*a1+4*a2+3*a3+2*a4+1.5*a5+1.5*a6+3*7a+2*a8+2*a9+1.5*a10+6*a11)/b
     *
     */
    public static function biomassDensity() {
        extract(static::$param);
        $ii = static::$coefficient;

        return ($ii[0] * $a1 + $ii[1] * $a2 + $ii[2] * $a3 + $ii[3] * $a4 + $ii[4] * $a5 + $ii[5] * $a6 + $ii[6] * $a7 + $ii[7] * $a8 + $ii[8] * $a9 + $ii[9] * $a10 + $ii[10] * $a11) / $b;
    }

    /**
     * @brief  有效生态用地面积比指数
     * @factor  100.5022*[a1+a2+a3+a4+a5+a6+a7+ a8+a9+a10+a11*0.7+a12*0.7+a13*0.7+a14*0.5]/S*100%
     */
    public static function effectiveEcologicalLandAreaRatioIndex(): string {
        extract(static::$param);
        $ii = static::$coefficient;

        $data = $ii[0] * ($a1 + $a2 + $a3 + $a4 + $a5 + $a6 + $a7 + $a8 + $a9 + $a10 + $a11 * $ii[1] + $a12 * $ii[2] + $a13 * $ii[3] + $a14 * $ii[4]) / $S;
        return static::format($data / ($ii[5] / 100));
    }

    /**
     * @brief  代表物种生境面积
     * @factor a/b*100%
     */
    public static function habitatAreaRepresentativeSpecies(): string {
        extract(static::$param);
        $coefficient = static::$coefficient;

        $data = $a * $b;
        return static::format($data / ($coefficient[0] / 100));
    }

    /**
     * @brief  生态廊道占比
     * @factor a/b*100%
     */
    public static function proportionEcologicalCorridor(): string {
        extract(static::$param);
        $coefficient = static::$coefficient;

        $data = $a * $b;
        return static::format($data / ($coefficient[0] / 100));
    }

    /**
     * @brief  重点保护生物指数
     * @factor  0.1510 × a+ 13.2142
     */
    public static function indexKeyProtectedOrganisms() {
        extract(static::$param);
        $coefficient = static::$coefficient;

        return $coefficient[0] * $a + $coefficient[1];
    }


    /**
     * @brief  综合物种指数
     * @factor  1/3（a1/b1+a2/b2+a3/b3)
     */
    public static function compositeSpeciesIndex() {
        extract(static::$param);
        $coefficient = static::$coefficient;

        return $coefficient[0] * ($a1 / $b1 + $a2 / $b2 + $a3 / $b3);
    }

    /**
     * @brief  人体舒适度
     * @factor  (1.818b+ 18.18)(0.88 + 0.002c)+(b- 32) / (45 -b)- 3.2d+ 18.2
     */
    public static function humanComfort() {
        extract(static::$param);
        $ii = static::$coefficient;
        return ($ii[0] * $b + $ii[1])*($ii[2] + $ii[3] * $c) + ($b - $ii[4]) / ($ii[5] - $b) - $ii[6] * $d + $ii[7];
    }

    /**
     * @brief   绿视率
     * @factor  b/c*100%
     */
    public static function greenVisionRate(): string {
        extract(static::$param);
        $coefficient = static::$coefficient;

        $data = $b * $c;
        return static::format($data / ($coefficient[0] / 100));
    }

    /**
     * @brief  绿道品质
     * @factor  b+c
     */
    public static function greenwayQuality() {
        extract(static::$param);
        return $a + $b;
    }

    /**
     * @brief  林荫道完整度
     * @factor b/c*100%
     */
    public static function mallIntegrity(): string {
        extract(static::$param);
        $coefficient = static::$coefficient;

        $data = $b * $c;
        return static::format($data / ($coefficient[0] / 100));
    }

    /**
     * @brief  创建工作岗位的数量
     * @factor  a+b
     */
    public static function numberJobsCreated() {
        extract(static::$param);
        return ($a + $b);
    }

    /**
     * @brief  植物节能减排效益
     * @factor  b*c*d*e
     */
    public static function benefitsPlantEnergyConservationEmissionReduction() {
        extract(static::$param);
        return $b * $c * $d * $e;
    }

    /**
     * @brief  地质灾害易发性
     * @factor b×0.3+c×0.2+d×0.25-e×0.15+f×0.1
     */
    public static function geologicalHazardSusceptibility() {
        extract(static::$param);
        return $b * 0.3 + $c * 0.2 + $d * 0.25 - $e * 0.15 + $f * 0.1;
    }

    /**
     * @brief  教育项目类型（种）
     * @factor
     */

    public static function statisticsTeachNumber(){

        extract(static::$param);

        $number = 0;
        if($a>3){
            $number =5;
        }
        elseif ($a==2||$a==3){
            $number=3;
        }else{
            $number=1;
        }

        return $number;
    }


    /**
     * @brief  访客量（人次/月）
     * @factor
     */

    public static function statisticsVictors(){

        extract(static::$param);

        if($a>=100){
            $number1 =5;
        }
        elseif ($a>50&&$a<100){
            $number1=3;
        }else{
            $number1=1;
        }


        if($b>=500){
            $number2 =5;
        }
        elseif ($b>200&&$b<500){
            $number2=3;
        }else{
            $number2=1;
        }


        if($c>=1000){
            $number3 =5;
        }
        elseif ($c>500&&$c<1000){
            $number3=3;
        }else{
            $number3=1;
        }

        return ($number1+$number2+$number3)/3;

    }


    /**
     * @brief  访客量（人次/月）
     * @factor
     */
    public static function statisticsOldTree(){

        extract(static::$param);

        if($a>=30){
            $number =5;
        }
        elseif ($a>10&&$a<30){
            $number=3;
        }else{
            $number=1;
        }

        return $number;

    }

    // 不进行格式化操作
    private static function format($number) {
        return $number;
    }
}
