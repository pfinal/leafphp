<?php

namespace Middleware;

use Leaf\Request;
use Leaf\Response;

/**
 * HTTP Basic Authentication
 *
 * 浏览器收到"401 Unauthorized"时，会弹出用户名／密码输入窗口，
 * 输入之后，每次请求，将带上"Authorization:Basic xxx"信息，基中xxx为用户名加密码的Base64编码
 * 例如"Authorization:Basic YWRtaW46MTExMTEx"，使用base64_decode("YWRtaW46MTExMTEx")解码后，得到"admin:111111"
 *
 * @link http://www.php.net/manual/zh/features.http-auth.php
 */
class AuthWithBasicMiddleware
{
    public function handle(Request $request, \Closure $next)
    {
        $username = $request->headers->get('PHP_AUTH_USER');
        $password = $request->headers->get('PHP_AUTH_PW');

        if ($this->checkUser($username, $password)) {
            return $next($request);
        }

        return new Response('Invalid credentials.', 401, ['WWW-Authenticate' => 'Basic']);
    }

    protected function checkUser($username, $password)
    {
        return false;
    }
}