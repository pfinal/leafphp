<?php

namespace Service;

use Entity\User;
use Leaf\Redirect;
use Leaf\Request;
use Leaf\Session;
use Leaf\Url;
use Leaf\DB;
use Leaf\View;

trait AuthUser
{
    /**
     * 登录视图
     */
    protected function loginView()
    {
        return View::render('auth/login.twig');
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

        if ($request->isMethod('POST')) {

            /** @var $user User */
            $user = DB::table(User::tableName())
                ->where('username=?', [$request->get('username')])
                ->asEntity(User::className())
                ->findOne();

            if (md5($request->get('password')) === $user['password_hash']) {

                Auth::login($user, $request->get('remember'));

                return Redirect::to(Session::getFlash('returnUrl', Url::to('/')));
            }

            Session::setFlash('message', '用户名或密码不匹配');
        }

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
}