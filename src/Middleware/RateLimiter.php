<?php

namespace Middleware;

use PFinal\Cache\CacheInterface;

class RateLimiter
{
    /**
     * @var \PFinal\Cache\CacheInterface
     */
    protected $cache;

    /**
     * Create a new rate limiter instance.
     *
     * @param \PFinal\Cache\CacheInterface $cache
     */
    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Determine if the given key has been "accessed" too many times.
     *
     * @param  string $key
     * @param  int $maxAttempts
     * @param  float|int $decayMinutes
     * @return bool
     */
    public function tooManyAttempts($key, $maxAttempts, $decayMinutes = 1)
    {
        if ($this->cache->get($key . ':lockout')) {
            return true;
        }

        if ($this->attempts($key) >= $maxAttempts) {
            $this->lockout($key, $decayMinutes);

            $this->resetAttempts($key);

            return true;
        }

        return false;
    }

    /**
     * Add the lockout key to the cache.
     *
     * @param  string $key
     * @param  int $decayMinutes
     * @return void
     */
    protected function lockout($key, $decayMinutes)
    {
        $this->cache->add(
            $key . ':lockout', time() + $decayMinutes * 60, $decayMinutes * 60
        );
    }

    /**
     * Increment the counter for a given key for a given decay time.
     *
     * @param  string $key
     * @param  int $decayMinutes
     * @return int
     */
    public function hit($key, $decayMinutes = 1)
    {
        $this->cache->add($key, 0, $decayMinutes * 60);

        return (int)$this->cache->increment($key);
    }

    /**
     * Get the number of attempts for the given key.
     *
     * @param  string $key
     * @return mixed
     */
    public function attempts($key)
    {
        return (int)$this->cache->get($key);
    }

    /**
     * Reset the number of attempts for the given key.
     *
     * @param  string $key
     * @return mixed
     */
    public function resetAttempts($key)
    {
        return $this->cache->delete($key);
    }

    /**
     * Get the number of retries left for the given key.
     *
     * @param  string $key
     * @param  int $maxAttempts
     * @return int
     */
    public function retriesLeft($key, $maxAttempts)
    {
        $attempts = $this->attempts($key);

        return $maxAttempts - $attempts;
    }

    /**
     * Clear the hits and lockout for the given key.
     *
     * @param  string $key
     * @return void
     */
    public function clear($key)
    {
        $this->resetAttempts($key);

        $this->cache->delete($key . ':lockout');
    }

    /**
     * Get the number of seconds until the "key" is accessible again.
     *
     * @param  string $key
     * @return int
     */
    public function availableIn($key)
    {
        return $this->cache->get($key . ':lockout') - time();
    }
}
