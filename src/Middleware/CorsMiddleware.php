<?php

namespace Middleware;

class CorsMiddleware
{
    public function handle(\Leaf\Request $request, \Closure $next)
    {
        $response = $next($request);
        if (!$response instanceof \Symfony\Component\HttpFoundation\Response) {
            $response = new \Symfony\Component\HttpFoundation\Response($response);
        }
        $response->headers->add(array('Access-Control-Allow-Origin' => '*'));
        return $response;
    }
}