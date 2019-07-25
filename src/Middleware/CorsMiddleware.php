<?php

namespace Middleware;

class CorsMiddleware
{
    /**
     * @param \Leaf\Request  $request
     * @param \Closure $next
     * @return mixed|\Symfony\Component\HttpFoundation\Response
     */
    public function handle($request, \Closure $next)
    {
        //https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
        //https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/OPTIONS
        $headers = array(
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'HEAD, GET, POST, PUT, PATCH, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => 'Origin, No-Cache, XMLHttpRequest, X-Requested-With, If-Modified-Since, Pragma, Last-Modified, Cache-Control, Expires, Content-Type, X-E4M-With, Authorization, X-PINGOTHER, Accept',
            'Access-Control-Max-Age' => 86400,
        );
        if ($request->isMethod('options')) {
            $response = new \Symfony\Component\HttpFoundation\Response();
            $response->headXMLHttpRequesters->add($headers);
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
