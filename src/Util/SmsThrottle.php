<?php

namespace Util;

use Leaf\Cache;
use Leaf\Util;

/**
 * 手机验证码频率限制
 *
 * @author 邹义良
 */
class SmsThrottle extends \Middleware\ThrottleMiddleware
{
    //限制[bucket]分钟内最多[maxCount]次
    public $bucket = 60; //分钟
    public $maxCount = 5;

    public $interval = 60;//N秒内，不允重复发送

    /**
     * @return static
     */
    public static function instance()
    {
        return new static(new \Middleware\RateLimiter(\Leaf\Application::$app['cache']));
    }

    /**
     * 是否需要启用图型验证码
     *
     * @return bool
     */
    public function enableCaptcha($mobile, array $extra = array())
    {
        //1分钟内发送的总次数超10次
        $key = 'SmsThrottleAll';
        $maxAttempts = 10; //次
        $decayMinutes = 1; //分钟
        if ($this->limiter->tooManyAttempts($key, $maxAttempts, $decayMinutes)) {
            //需要启用图型验证码
            return true;
        }
        $this->limiter->hit($key, $decayMinutes);

        //同一手机30分钟内重复发送
        $key = $mobile;
        $maxAttempts = 2;   //次
        $decayMinutes = 30; //分钟
        if ($this->limiter->tooManyAttempts($key, $maxAttempts, $decayMinutes)) {
            //需要启用图型验证码
            return true;
        }
        $this->limiter->hit($key, $decayMinutes);

        //不需要图型验证码
        return false;
    }

    /**
     * 是否允许发送
     *
     * @param string $mobile
     * @param string $error
     * @param array $extra 附加信息，例如 ip、client(客户端标识)
     * @return bool
     */
    public function check($mobile, array $extra = array(), &$error = '')
    {
        if (!Util::isMobile($mobile)) {
            $error = '手机号码格式不正确';
            return false;
        }

        if (Cache::get($mobile . 'SmsVerifyTime') === true) {
            $error = $this->interval . '秒内请勿重复发送验证码';

            return false;
        }

        //限制N分钟内最多M次
        $history = Cache::get($mobile . 'SmsVerifyHistory');
        if (empty($history)) {
            $history = array();
        }
        foreach ($history as $key => $time) {
            if (time() - $time > 60 * $this->bucket) {
                unset($history[$key]);
            }
        }
        if (count($history) >= $this->maxCount) {
            $error = '您发送次数过多，请稍候再试';
            return false;
        }


        $history[] = time();
        Cache::set($mobile . 'SmsVerifyHistory', $history, 60 * 60 * 24);
        Cache::set($mobile . 'SmsVerifyTime', true, $this->interval);// N秒内请勿重复发送

        return true;
    }
}