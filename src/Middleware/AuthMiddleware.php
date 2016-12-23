<?php

namespace Middleware;

use Service\Auth;
use Leaf\Json;
use Leaf\Redirect;
use Leaf\Request;
use Leaf\Session;

/**
 * 用户验证中间件
 */
class AuthMiddleware
{
    public function handle(Request $request, \Closure $next)
    {
        // 尝试从cookie记住我登录，1个月内有效
        Auth::attemptFromRemember($request, 30);

        if (Auth::isGuest()) {

            // ajax请求
            if ($request->isXmlHttpRequest()) {
                return Json::renderWithFalse('LOGIN_REQUIRED'); //unauthorized
            }

            // 记住当前url
            Session::setFlash('returnUrl', $request->getRequestUri());

            return Redirect::to('login');
        }
        return $next($request);
    }
}