<?php

namespace App\Services;

class VerificationCode
{
    const KEY_TEMPLATE = 'verify_code_of_%s';

    /**
     * 创建并存储验证码
     * @return int
     */
    public function create($phone)
    {
        $code = mt_rand(1000, 9999);

        \Log::debug("验证码：{$phone}:{$code}");

        Cache::put(sprintf(self::KEY_TEMPLATE, $phone), $code, 10);

        if (app()->environment('production')) {
            // 发送短信
        }

        return $code;
    }

    /**
     * 检查手机号与验证码是否匹配.
     *
     * @param string $phone
     * @param int    $code
     *
     * @return bool
     */
    public function validate($phone, $code)
    {
        if (empty($phone) || empty($code)) {
            return false;
        }

        $key = sprintf(self::KEY_TEMPLATE, $phone);

        $cachedCode = Cache::get($key);

        \Log::debug('cached verify code', ['key' => $key, 'cached' => $cachedCode, 'input' => $code]);

        return strval($cachedCode) === strval($code);
    }
}
