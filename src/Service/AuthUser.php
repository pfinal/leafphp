<?php

namespace Service;

use Entity\User;
use Leaf\Exception\HttpException;
use Leaf\Mail;
use Leaf\Redirect;
use Leaf\Request;
use Leaf\Route;
use Leaf\Session;
use Leaf\Url;
use Leaf\DB;
use Leaf\Util;
use Leaf\Validator;
use Leaf\View;

/**
 * 用户认证
 */
trait AuthUser
{

    /**
     * 注册路由
     */
    public static function routes()
    {
        Route::any('register', get_called_class() . '@register');
        Route::any('admin/login', get_called_class() . '@login');
        Route::any('admin/logout', get_called_class() . '@logout');
        Route::any('admin/password/forgot', get_called_class() . '@forgot');
        Route::any('admin/password/reset', get_called_class() . '@reset');
    }

    /**
     * 是否开启注册功能
     *
     * @return bool
     */
    protected function enableRegister()
    {
        return false;
    }

    /**
     * 注册视图
     */
    protected function registerView()
    {
        return View::render('auth/register.twig');
    }

    /**
     * 登录视图
     */
    protected function loginView()
    {
        return View::render('auth/login.twig');
    }

    /**
     * 忘记密码视图
     */
    protected function forgotView()
    {
        return View::render('auth/password/forgot.twig');
    }

    /**
     * 重置密码视图
     */
    protected function resetView()
    {
        return View::render('auth/password/reset.twig');
    }

    /**
     * 重定向
     *
     * @param string $scene login|register|logout
     * @return string
     */
    protected function redirectTo($scene)
    {
        return Url::to('/');
    }

    /**
     * 生成密码哈希
     * @param string $password
     * @return string
     */
    protected function passwordHash($password)
    {
        //return password_hash($password, PASSWORD_BCRYPT);
        return md5($password);
    }

    /**
     * 验证密码哈希
     * @param string $password
     * @param string $passwordHash
     * @return bool
     */
    protected function passwordVerify($password, $passwordHash)
    {
        //return password_verify($password, $passwordHash);
        if (strlen($password) < 4 || strlen($password) > 50) {
            return false;
        }
        return $this->passwordHash($password) === $passwordHash;
    }

    /**
     * 注册
     */
    public function register(Request $request)
    {
        //已登录状态，直接跳转
        if (Auth::check()) {
            return Redirect::to($this->redirectTo('register'));
        }

        if ($request->isMethod('get')) {
            return $this->registerView();
        }

        if (!$this->enableRegister()) {
            Session::setFlash('message', '系统未开放注册功能');
            return $this->registerView();
        }

        $data = $request->all();

        $rule = [
            [['email', 'nickname'], 'trim'],
            [['email', 'nickname', 'password', 'password_confirmation'], 'required'],
            ['email', 'email'],
            ['nickname', 'string', 'length' => [2, 10]],
            [['password', 'password_confirmation'], 'string', 'length' => [6, 20]],
            ['password', 'compare', 'compareValue' => $data['password_confirmation'], 'message' => '两次密码必须相同'],
            ['email', 'unique', 'table' => User::tableName(), 'field' => 'email'],
        ];

        $labels = [
            'email' => '邮箱',
            'nickname' => '昵称',
            'password' => '密码',
            'password_confirmation' => '确认密码',
        ];

        if (!Validator::validate($data, $rule, $labels)) {
            Session::setFlash('message', Validator::getFirstError());
            return $this->registerView();
        }

        $data['password_hash'] = $this->passwordHash($data['password']);
        $data['status'] = User::STATUS_ENABLE;
        $data['created_at'] = $data['updated_at'] = date('Y-m-d H:i:s');

        unset($data['password']);
        unset($data['password_confirmation']);

        $userId = DB::table(User::tableName())->insertGetId($data);

        if ($userId > 0) {
            Auth::loginUsingId($userId);
            return Redirect::to($this->redirectTo('register'));
        } else {
            Session::setFlash('message', '系统错误，注册失败');
            return $this->registerView();
        }
    }

    /**
     * 登录
     */
    public function login(Request $request)
    {
        //已登录状态，直接跳转
        if (Auth::check()) {
            return Redirect::to($this->redirectTo('login'));
        }

        if ($request->isMethod('get')) {
            return $this->loginView();
        }

        $query = DB::table(User::tableName())
            ->asEntity(User::className());

        $loginId = $request->get('loginId', '');

        //同时支持邮箱手机和帐号登录
        if (Util::isEmail($loginId)) {
            $query->where('email=?', [$loginId]);
        } else if (Util::isMobile($loginId)) {
            $query->where('mobile=?', [$loginId]);
        } else {
            $query->where('username=?', [$loginId]);
        }

        /** @var $user User */
        $user = $query->where(['status' => User::STATUS_ENABLE])->findOne();

        if ($this->passwordVerify($request->get('password'), $user['password_hash'])) {

            Auth::login($user, $request->get('remember'));

            Session::set('last_login_at', @date('Y-m-d H:i:s'));

            return Redirect::to(Session::getFlash('returnUrl', $this->redirectTo('login')));
        }

        Session::setFlash('message', '用户名或密码不匹配');

        return $this->loginView();
    }

    /**
     * 注销
     */
    public function logout()
    {
        Auth::logout();
        return Redirect::to($this->redirectTo('logout'));
    }

    /**
     * 邮件找回密码
     */
    public function forgot(Request $request)
    {
        if (Auth::check()) {
            return Redirect::to($this->redirectTo('login')); //已登录
        }

        if ($request->isMethod('get')) {
            return $this->forgotView();
        }

        $email = $request->get('email');

        if (!Util::isEmail($email)) {
            Session::setFlash('message', '请输入正确邮箱');
            return $this->forgotView();
        }

        $user = DB::table(User::tableName())->where('email=?', [$email])->findOne();

        if ($user == null) {
            Session::setFlash('message', '邮箱未注册');
            return $this->forgotView();
        }

        $token = strtolower(str_replace('-', '', Util::guid()));
        $bool = DB::table('password_reset')->insert(['email' => $user['email'], 'token' => $token, 'created_at' => date('Y-m-d H:i:s')]);
        if (!$bool) {
            Session::setFlash('message', '系统错误，请稍后再试');
            return $this->forgotView();
        }

        $data = [
            'site' => '',//站点的名称
            'username' => $user['nickname'] ? $user['nickname'] : $user['username'],
            'url' => Url::to('password/reset', ['token' => $token], true),
        ];

        $bool = Mail::send($email, '密码重置连接', View::render('auth/password/email.twig', $data));
        if ($bool) {
            Session::setFlash('message', '邮件发送成功，请根据邮件提示操作');
        } else {
            Session::setFlash('message', '邮件发送失败');
        }

        return $this->forgotView();
    }

    /**
     * 重置密码
     */
    public function reset(Request $request)
    {
        if (Auth::check()) {
            return Redirect::to($this->redirectTo('login')); //已登录
        }

        if ($request->isMethod('get')) {
            return $this->resetView();
        }

        $token = $request->get('token');
        $email = $request->get('email');
        $password = $request->get('password');
        $passwordConfirm = $request->get('password_confirm');

        if ($password !== $passwordConfirm) {
            Session::setFlash('message', '两次密码不匹配');
            return $this->resetView();
        }

        $info = DB::table('password_reset')->where('token=?', [$token])->findOne();

        if ($info == null || time() - strtotime($info['created_at']) > 60 * 60 * 24) {
            Session::setFlash('message', '连接已失效');
            return $this->resetView();
        }

        if ($email !== $info['email']) {
            Session::setFlash('message', '邮箱错误');
            return $this->resetView();
        }

        $user = DB::table(User::tableName())->asEntity(User::className())->where('email=?', [$email])->findOne();
        if ($user == null) {
            throw new HttpException(500, '无此用户信息');
        }

        $data = [
            'password_hash' => static::passwordHash($password),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if (DB::table(User::tableName())->where('id=?', [$user->getId()])->update($data)) {

            // 让"记住我"失效
            Auth::saveRememberToken($user->getId(), '');

            // 清空token
            DB::table('password_reset')->where('token=?', [$token])->delete();

            Session::setFlash('message', '重置密码成功');
        } else {
            Session::setFlash('message', '系统错误');
        }

        return $this->resetView();
    }

}