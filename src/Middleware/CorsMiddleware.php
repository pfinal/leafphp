<?php

namespace Middleware;

class CorsMiddleware
{
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Closure $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle($request, \Closure $next)
    {
        //https://developer.mozilla.org/zh-CN/docs/Web/HTTP/Access_control_CORS

        $headers = array(
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET,POST,PUT,PATCH,DELETE,OPTIONS',
            //'Access-Control-Allow-Headers' => 'Authorization',
            'Access-Control-Max-Age' => 86400,  // 以秒为单位的缓存时间
        );

        if ($request->isMethod('options')) {
            $response = new \Symfony\Component\HttpFoundation\Response();

            $requestHeaders = $request->headers->get('access-control-request-headers');
            if ($requestHeaders) {
                $headers['Access-Control-Allow-Headers'] = $requestHeaders;
            }

            $response->headers->add($headers);
            return $response;
        }

        $response = $next($request);
        if (!$response instanceof \Symfony\Component\HttpFoundation\Response) {
            $response = new \Symfony\Component\HttpFoundation\Response($response);
        }
        $response->headers->add($headers);
        return $response;
    }
}
