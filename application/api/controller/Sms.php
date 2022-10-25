<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\library\Sms as Smslib;
use app\common\model\User;
use think\Hook;

/**
 * 手机短信接口
 */
class Sms extends Api
{
    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';

    /**
     * 发送验证码
     *
     * @ApiMethod (POST)
     * @param string $mobile 手机号
     * @param string $event 事件名称
     */
    public function send()
    {
        $mobile = $this->request->param("mobile");
        $event = $this->request->param("event");
        $event = $event ? $event : 'register';

        if (!$mobile || !\think\Validate::regex($mobile, "^1\d{10}$")) {
            return json(['code' => 1, 'message' => '手机号不正确', 'data' => []]);
        }
        $last = Smslib::get($mobile, $event);
        if ($last && time() - $last['createtime'] < 60) {
            return json(['code' => 1, 'message' => '发送频繁', 'data' => []]);
        }
        $ipSendTotal = \app\common\model\Sms::where(['ip' => $this->request->ip()])->whereTime('createtime', '-1 hours')->count();
        if ($ipSendTotal >= 5) {
            return json(['code' => 1, 'message' => '发送频繁', 'data' => []]);
        }
        if ($event) {
            $userinfo = User::getByMobile($mobile);
            if ($event == 'register' && $userinfo) {
                //已被注册
                return json(['code' => 1, 'message' => '已被注册', 'data' => []]);
            } elseif (in_array($event, ['changemobile']) && $userinfo) {
                //被占用
                return json(['code' => 1, 'message' => '已被占用', 'data' => []]);
            } elseif (in_array($event, ['changepwd', 'resetpwd']) && !$userinfo) {
                //未注册
                return json(['code' => 1, 'message' => '未注册', 'data' => []]);
            }
        }
        if (!Hook::get('sms_send')) {
            return json(['code' => 1, 'message' => '发送失败', 'data' => []]);
        }
        $ret = Smslib::send($mobile, null, $event);
        if ($ret) {
            return json(['code' => 0, 'message' => '发送成功', 'data' => []]);
        } else {
            return json(['code' => 1, 'message' => '发送失败!', 'data' => []]);
        }
    }

    /**
     * 检测验证码
     *
     * @ApiMethod (POST)
     * @param string $mobile 手机号
     * @param string $event 事件名称
     * @param string $captcha 验证码
     */
    public function check()
    {
        $mobile = $this->request->param("mobile");
        $event = $this->request->param("event");
        $event = $event ? $event : 'register';
        $captcha = $this->request->param("captcha");

        if (!$mobile || !\think\Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('手机号不正确'));
        }
        if ($event) {
            $userinfo = User::getByMobile($mobile);
            if ($event == 'register' && $userinfo) {
                //已被注册
                $this->error(__('已被注册'));
            } elseif (in_array($event, ['changemobile']) && $userinfo) {
                //被占用
                $this->error(__('已被占用'));
            } elseif (in_array($event, ['changepwd', 'resetpwd']) && !$userinfo) {
                //未注册
                $this->error(__('未注册'));
            }
        }
        $ret = Smslib::check($mobile, $captcha, $event);
        if ($ret) {
            $this->success(__('成功'));
        } else {
            $this->error(__('验证码不正确'));
        }
    }
}
