<?php

namespace app\api\controller;

use app\admin\model\City as cityModel;
use app\common\controller\Api;
use app\common\library\Ems;
use app\common\library\Sms;
use app\common\service\ImagesService;
use fast\Random;
use think\Config;
use think\Cookie;
use think\Env;
use think\Request;
use think\Validate;

/**
 * 会员接口
 */
class User extends Api {
    protected $noNeedLogin = ['login', 'mobilelogin', 'register', 'emailRegister', 'mobileRegister', 'resetpwd', 'changeemail', 'changemobile', 'third'];
    protected $noNeedRight = '*';

    public function _initialize() {
        parent::_initialize();

        if (!Config::get('fastadmin.usercenter')) {
            $this->error(__('User center already closed'));
        }

    }

    /**
     * 会员中心
     */
    public function index() {
        //$this->success('', ['welcome' => $this->auth->nickname]);

        $user           = $this->auth->getUserinfo();

        if(strlen($user['avatar'])>100) {
            $user['avatar'] = '';
        }else{
            $user['avatar'] = ImagesService::getAvatar($user['avatar']);
        }
        $data           = ['userinfo' => $user];
        return json(['code' => 0, 'data' => $data, 'message' => 'OK']);
    }

    /**
     * 会员登录
     *
     * @ApiMethod (POST)
     * @param string $account 账号
     * @param string $password 密码
     */
    public function login(Request $request) {
        $account  = $request->param('account');
        $password = $request->param('password');
        if (!$account || !$password) {
            $this->error(__('Invalid parameters'));
        }
        $ret = $this->auth->login($account, $password);

        if ($ret) {
            $user           = $this->auth->getUserinfo();

            if(strlen($user['avatar'])>100) {
                $user['avatar'] = '';
            }else{
                $user['avatar'] = ImagesService::getAvatar($user['avatar']);
            }
            $data           = ['userinfo' => $user];
            return json(['code' => 0, 'data' => $data, 'message' => 'OK']);
        } else {
            return json(['code' => 1, 'data' => [], 'message' => '登录失败']);
        }
    }


    /**
     * 手机验证码登录
     *
     * @ApiMethod (POST)
     * @param string $mobile 手机号
     * @param string $captcha 验证码
     */
    public function mobilelogin() {
        $mobile  = $this->request->post('mobile');
        $captcha = $this->request->post('code');
        if (!$mobile || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        if (!Sms::check($mobile, $captcha, 'login')) {
            $this->error(__('Captcha is incorrect'));
        }
        $user = \app\common\model\User::getByMobile($mobile);
        if ($user) {
            if ($user->status != 'normal') {
                $this->error(__('Account is locked'));
            }
            //如果已经有账号则直接登录
            $ret = $this->auth->direct($user->id);
        } else {
            $ret = $this->auth->register($mobile, Random::alnum(), '', $mobile, []);
        }
        if ($ret) {
            Sms::flush($mobile, 'mobilelogin');

            $user           = $this->auth->getUserinfo();

            if(strlen($user['avatar'])>100) {
                $user['avatar'] = '';
            }else{
                $user['avatar'] = ImagesService::getAvatar($user['avatar']);
            }
            $data           = ['userinfo' => $user];
            $this->success(__('Logged in successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 注册会员
     *
     * @ApiMethod (POST)
     * @param string $username 用户名
     * @param string $password 密码
     * @param string $email 邮箱
     * @param string $mobile 手机号
     * @param string $code 验证码
     */
    public function register() {
        $username = $this->request->post('username');
        $password = $this->request->post('password');
        $email    = $this->request->post('email');
        $mobile   = $this->request->post('mobile');
        $code     = $this->request->post('code');
        if (!$username || !$password) {
            $this->error(__('Invalid parameters'));
        }
        if ($email && !Validate::is($email, "email")) {
            $this->error(__('Email is incorrect'));
        }
        if ($mobile && !Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        $ret = Sms::check($mobile, $code, 'register');
        if (!$ret) {
            $this->error(__('Captcha is incorrect'));
        }
        $ret = $this->auth->register($username, $password, $email, $mobile, []);
        if ($ret) {
            $user           = $this->auth->getUserinfo();
            $user['avatar'] =  ImagesService::getBaseUrl() . $ret['avatar'];
            $data           = ['userinfo' => $user];
            $this->success(__('Sign up successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }


    /**
     * mobile注册会员
     * @ApiMethod (POST)
     * @param string $email 邮箱
     * @param string $code 验证码
     */

    public function mobileRegister() {

        $mobile   = $this->request->post('mobile');
        $code     = $this->request->param('code');
        $password = $this->request->param('password');

        if ($mobile && !Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }

        if (!$mobile)
            $this->error(__('手机号不正确！！'));
        //        $ret = Sms::check($mobile, $code, 'register');
        //        if (!$ret) {
        //            $this->error(__('Captcha is incorrect'));
        //        }

        $user = \app\common\model\User::where(['mobile' => $mobile])->select()->toArray();
        if ($user) {
            $this->error(__('mobile is exist'));
        }

        $ret = $this->auth->register($mobile, $password, '', $mobile, []);
        if ($ret) {
            $user           = $this->auth->getUserinfo();
            $user['avatar'] =  $ret['avatar'];
            $data           = ['userinfo' => $user];
            $this->success(__('Sign up successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }


    /**
     * email注册会员
     * @ApiMethod (POST)
     * @param string $email 邮箱
     * @param string $code 验证码
     */

    public function emailRegister() {

        $email    = $this->request->param('email');
        $code     = $this->request->param('code');
        $password = $this->request->param('password');

        if ($email && !Validate::is($email, "email")) {
            $this->error(__('Email is incorrect'));
        }
        $user = \app\common\model\User::where(['email' => $email])->select()->toArray();
        if ($user) {
            $this->error(__('Email is exist'));
        }
        //        $ret = Ems::check($email,$code,'register');
        //        if($ret){
        //            $this->error(__('code is incorrect'i));
        //        }
        $ret = $this->auth->register($email, $password, $email, '', []);
        if ($ret) {
            $user = $this->auth->getUserinfo();
            $user['avatar'] = $ret['avatar'];
            $data = ['userinfo' => $user];
            $this->success(__('Sign up successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 退出登录
     * @ApiMethod (POST)
     */
    public function logout() {
        if (!$this->request->isPost()) {
            $this->error(__('Invalid parameters'));
        }
        $this->auth->logout();
        $this->success(__('Logout successful'));
    }

    /**
     * 修改会员个人信息
     *
     * @ApiMethod (POST)
     * @param string $avatar 头像地址
     * @param string $username 用户名
     * @param string $nickname 昵称
     * @param string $bio 个人简介
     */
    public function profile() {
        $param = $this->request->param();
        $user  = $this->auth->getUser();
        if (!$user) {
            $this->auth->init($param['token']);
            $user = $this->auth->getUser();
        }

        $username           = $this->request->param('username');
        $occupationName     = $this->request->param('occupation_name');
        $occupationCategory = $this->request->param('occupation_category');
        $province           = $this->request->param('province');
        $companyAddress     = $this->request->param('company_address');
        $city               = $this->request->param('city');
        $birthday           = $this->request->param('birthday');
        $gender             = $this->request->param('gender');
        $bio                = $this->request->param('bio');
        $avatar             = $this->request->param('avatar', '', 'trim,strip_tags,htmlspecialchars');
        if ($username) {
            $exists = \app\common\model\User::where('username', $username)->where('id', '<>', $this->auth->id)->find();
            if ($exists) {
                $this->error(__('Username already exists'));
            }
            $user->username = $username;
        }

        $user->bio                 = $bio;
        $user->occupation_name     = $occupationName;
        $user->occupation_category = $occupationCategory;
        $user->company_address     = $companyAddress;
        $user->province            = $province;
        $user->city                = $city;
        $user->birthday            = $birthday;
        $user->gender              = $gender;
        $user->avatar              = $avatar;
        $user->save();

        $data           = $this->auth->getUserinfo();
        if(strlen($data['avatar'])>100) {
            $data['avatar'] = '';
        }else{
            $data['avatar'] = ImagesService::getAvatar($data['avatar']);
        }
        $this->success('OK', $data, 0);
    }


    public function editAvatar() {

        $param = json_decode(file_get_contents("php://input"), 1);
        $user  = $this->auth->getUser();
        if (!$user) {
            $this->auth->init($param['token']);
            $user = $this->auth->getUser();
        }
        $avatar = $this->request->post('avatar', '', 'trim,strip_tags,htmlspecialchars');
        if (!$avatar) {
            $avatar = $param['avatar'];
        }
        $user->avatar = $avatar;
        $user->save();

        $data           = $this->auth->getUserinfo();
        if(strlen($data['avatar'])>100) {
            $data['avatar'] = '';
        }else{
            $data['avatar'] = ImagesService::getAvatar($data['avatar']);
        }
        $this->success('OK', $data, 0);
    }

    /**
     * 修改邮箱
     *
     * @ApiMethod (POST)
     * @param string $email 邮箱
     * @param string $captcha 验证码
     */
    public function changeemail() {
        $user    = $this->auth->getUser();
        $email   = $this->request->post('email');
        $captcha = $this->request->post('captcha');
        if (!$email || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::is($email, "email")) {
            $this->error(__('Email is incorrect'));
        }
        if (\app\common\model\User::where('email', $email)->where('id', '<>', $user->id)->find()) {
            $this->error(__('Email already exists'));
        }
        $result = Ems::check($email, $captcha, 'changeemail');
        if (!$result) {
            $this->error(__('Captcha is incorrect'));
        }
        $verification        = $user->verification;
        $verification->email = 1;
        $user->verification  = $verification;
        $user->email         = $email;
        $user->save();

        Ems::flush($email, 'changeemail');
        $this->success();
    }

    /**
     * 修改手机号
     *
     * @ApiMethod (POST)
     * @param string $mobile 手机号
     * @param string $captcha 验证码
     */
    public function changemobile() {
        $user    = $this->auth->getUser();
        $mobile  = $this->request->post('mobile');
        $captcha = $this->request->post('captcha');
        if (!$mobile || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        if (\app\common\model\User::where('mobile', $mobile)->where('id', '<>', $user->id)->find()) {
            $this->error(__('Mobile already exists'));
        }
        $result = Sms::check($mobile, $captcha, 'changemobile');
        if (!$result) {
            $this->error(__('Captcha is incorrect'));
        }
        $verification         = $user->verification;
        $verification->mobile = 1;
        $user->verification   = $verification;
        $user->mobile         = $mobile;
        $user->save();

        Sms::flush($mobile, 'changemobile');
        $this->success();
    }

    /**
     * 第三方登录
     *
     * @ApiMethod (POST)
     * @param string $platform 平台名称
     * @param string $code Code码
     */
    public function third() {
        $url      = url('user/index');
        $platform = $this->request->post("platform");
        $code     = $this->request->post("code");
        $config   = get_addon_config('third');
        if (!$config || !isset($config[$platform])) {
            $this->error(__('Invalid parameters'));
        }
        $app = new \addons\third\library\Application($config);
        //通过code换access_token和绑定会员
        $result = $app->{$platform}->getUserInfo(['code' => $code]);
        if ($result) {
            $loginret = \addons\third\library\Service::connect($platform, $result);
            if ($loginret) {
                $data = [
                    'userinfo'  => $this->auth->getUserinfo(),
                    'thirdinfo' => $result
                ];
                $this->success(__('Logged in successful'), $data);
            }
        }
        $this->error(__('Operation failed'), $url);
    }

    /**
     * 重置密码
     *
     * @ApiMethod (POST)
     * @param string $mobile 手机号
     * @param string $newpassword 新密码
     * @param string $captcha 验证码
     */
    public function resetpwd() {
        $type        = $this->request->post("type");
        $mobile      = $this->request->post("mobile");
        $email       = $this->request->post("email");
        $newpassword = $this->request->post("newpassword");
        $captcha     = $this->request->post("captcha");
        if (!$newpassword || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        //验证Token
        if (!Validate::make()->check(['newpassword' => $newpassword], ['newpassword' => 'require|regex:\S{6,30}'])) {
            $this->error(__('Password must be 6 to 30 characters'));
        }
        if ($type == 'mobile') {
            if (!Validate::regex($mobile, "^1\d{10}$")) {
                $this->error(__('Mobile is incorrect'));
            }
            $user = \app\common\model\User::getByMobile($mobile);
            if (!$user) {
                $this->error(__('User not found'));
            }
            $ret = Sms::check($mobile, $captcha, 'resetpwd');
            if (!$ret) {
                $this->error(__('Captcha is incorrect'));
            }
            Sms::flush($mobile, 'resetpwd');
        } else {
            if (!Validate::is($email, "email")) {
                $this->error(__('Email is incorrect'));
            }
            $user = \app\common\model\User::getByEmail($email);
            if (!$user) {
                $this->error(__('User not found'));
            }
            $ret = Ems::check($email, $captcha, 'resetpwd');
            if (!$ret) {
                $this->error(__('Captcha is incorrect'));
            }
            Ems::flush($email, 'resetpwd');
        }
        //模拟一次登录
        $this->auth->direct($user->id);
        $ret = $this->auth->changepwd($newpassword, '', true);
        if ($ret) {
            $this->success(__('Reset password successful'));
        } else {
            $this->error($this->auth->getError());
        }
    }
}
