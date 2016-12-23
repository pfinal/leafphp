<?php

namespace Service;

use Entity\User;
use Leaf\Exception\HttpException;
use Leaf\Mail;
use Leaf\Redirect;
use Leaf\Request;
use Leaf\Session;
use Leaf\Url;
use Leaf\DB;
use Leaf\Util;
use Leaf\View;

trait AuthUser
{
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
            return Redirect::to(Url::to('/'));
        }

        if ($request->isMethod('get')) {
            return $this->registerView();
        }

        Session::setFlash('message', '系统未开放注册功能');
        return $this->registerView();
    }

    /**
     * 登录
     */
    public function login(Request $request)
    {
        //已登录状态，直接跳转
        if (Auth::check()) {
            return Redirect::to(Url::to('/'));
        }

        if ($request->isMethod('get')) {
            return $this->loginView();
        }

        $query = DB::table(User::tableName())
            ->asEntity(User::className());

        $loginId = $request->get('loginId', '');

        //同时支持邮箱和帐号登录
        if (Util::isEmail($loginId)) {
            $query->where('email=?', [$loginId]);
        } else {
            $query->where('username=?', [$loginId]);
        }

        /** @var $user User */
        $user = $query->findOne();

        if ($this->passwordVerify($request->get('password'), $user['password_hash'])) {

            Auth::login($user, $request->get('remember'));

            return Redirect::to(Session::getFlash('returnUrl', Url::to('/')));
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
        return Redirect::to(Url::to('/'));
    }

    /**
     * 邮件找回密码
     */
    public function forgot(Request $request)
    {
        if (Auth::check()) {
            return Redirect::to(Url::to('/')); //已登录
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
            return Redirect::to(Url::to('/')); //已登录
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