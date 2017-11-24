<?php

namespace Middleware;

use Leaf\Application;
use Leaf\Exception\HttpException;
use Leaf\Log;
use Leaf\Request;
use Leaf\Session;
use Leaf\Util;

/**
 * CSRF保护 防止跨站请求伪造攻击
 *
 * form:
 * <form method="POST" action="/profile">
 *      <input type="hidden" name="_token" value="{{ csrf_token() }}">
 * </form>
 *
 * ajax:
 * <meta name="csrf-token" content="{{ csrf_token() }}">
 * $.ajaxSetup({
 *     headers: {
 *         'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
 *     }
 * });
 */
class CsrfMiddleware
{
    public function handle(Request $request, \Closure $next)
    {
        if (in_array($request->getMethod(), ['HEAD', 'GET', 'OPTIONS']) || $this->tokensMatch($request)) {

            Application::$app->extend('twig', function ($twig, $app) {
                $twig->addFunction(new \Twig_SimpleFunction('csrf_token', function () use ($app) {
                    return static::getTokenFromSession();
                }));
                return $twig;
            });

            return $next($request);
        }

        throw new HttpException(403, 'Csrf token mismatch.');
    }

    protected function tokensMatch(Request $request)
    {
        return self::getTokenFromRequest($request) == static::getTokenFromSession();
    }

    public static function getTokenFromRequest(Request $request)
    {
        $token = $request->get('_token', $request->headers->get('X-CSRF-TOKEN'));

        return $token;
    }

    public static function getTokenFromSession()
    {
        $token = Session::get('_token');
        if ($token == null) {
            $token = Util::random(40);
            Session::set('_token', $token);
        }
        return $token;
    }
}