<?php

namespace Middleware;

use Symfony\Component\HttpFoundation\Response;

class ThrottleMiddleware
{
    /**
     * The rate limiter instance.
     *
     * @var RateLimiter
     */
    protected $limiter;

    /**
     * Create a new request throttler.
     *
     * @param  RateLimiter $limiter
     */
    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    /**
     * Handle an incoming request.
     *
     * $app['PFinal\Cache\CacheInterface'] = function () use ($app) {
     *     return $app['cache'];
     * };
     * $app['throttle'] = 'Middleware\ThrottleMiddleware';
     * Route::group(['middleware' => ['throttle:60,1']], function () {});
     *
     * @param  \Leaf\Request $request
     * @param  \Closure $next
     * @param  int $maxAttempts
     * @param  float|int $decayMinutes
     * @return mixed
     */
    public function handle($request, \Closure $next, $maxAttempts = 60, $decayMinutes = 1)
    {
        $key = $this->resolveRequestSignature($request);

        if ($this->limiter->tooManyAttempts($key, $maxAttempts, $decayMinutes)) {
            return $this->buildResponse($key, $maxAttempts);
        }

        $this->limiter->hit($key, $decayMinutes);

        $response = $next($request);

        if (!$response instanceof \Symfony\Component\HttpFoundation\Response) {
            $response = new \Symfony\Component\HttpFoundation\Response($response);
        }

        return $this->addHeaders(
            $response, $maxAttempts,
            $this->calculateRemainingAttempts($key, $maxAttempts)
        );
    }

    /**
     * Resolve request signature.
     *
     * @param  \Leaf\Request $request
     * @return string
     */
    protected function resolveRequestSignature($request)
    {
        $routeInfo = $request->route();

        if ($routeInfo == null) {
            throw new \RuntimeException('Unable to generate fingerprint. Route unavailable.');
        }

        return 'throttle:' . sha1(implode('|', array_merge(
                (array)$routeInfo['method'],
                array($routeInfo['uri'], $request->getClientIp())
            )));
    }

    /**
     * Create a 'too many attempts' response.
     *
     * @param  string $key
     * @param  int $maxAttempts
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function buildResponse($key, $maxAttempts)
    {
        $response = new Response('Too Many Attempts.', 429);

        $retryAfter = $this->limiter->availableIn($key);

        return $this->addHeaders(
            $response, $maxAttempts,
            $this->calculateRemainingAttempts($key, $maxAttempts, $retryAfter),
            $retryAfter
        );
    }

    /**
     * Add the limit header information to the given response.
     *
     * @param  \Symfony\Component\HttpFoundation\Response $response
     * @param  int $maxAttempts
     * @param  int $remainingAttempts
     * @param  int|null $retryAfter
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function addHeaders(Response $response, $maxAttempts, $remainingAttempts, $retryAfter = null)
    {
        $headers = [
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => $remainingAttempts,
        ];

        if (!is_null($retryAfter)) {
            $headers['Retry-After'] = $retryAfter;
            $headers['X-RateLimit-Reset'] = time() + $retryAfter;
        }

        $response->headers->add($headers);

        return $response;
    }

    /**
     * Calculate the number of remaining attempts.
     *
     * @param  string $key
     * @param  int $maxAttempts
     * @param  int|null $retryAfter
     * @return int
     */
    protected function calculateRemainingAttempts($key, $maxAttempts, $retryAfter = null)
    {
        if (is_null($retryAfter)) {
            return $this->limiter->retriesLeft($key, $maxAttempts);
        }

        return 0;
    }
}
