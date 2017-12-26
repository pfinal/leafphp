<?php

namespace Middleware;

class CorsMiddleware
{
    public function handle(\Leaf\Request $request, \Closure $next)
    {
        //https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
        //https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/OPTIONS
        $headers = array(
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'HEAD, GET, POST, PUT, PATCH, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => 'X-PINGOTHER, Content-Type',
            'Access-Control-Max-Age' => 86400,
        );

        if ($request->isMethod('options')) {
            $response = new \Symfony\Component\HttpFoundation\Response();
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