<?php

namespace Tax\Invoice;

class SignUtil
{
    /**
     * 生成签名
     *
     * @param string $method HTTP方法
     * @param string $path 请求路径
     * @param string $randomString 随机字符串
     * @param string $timestamp 时间戳
     * @param string $appKey AppKey
     * @param string $appSecret AppSecret
     * @return string
     */
    public static function generateSign(string $method, string $path, string $randomString, string $timestamp, string $appKey, string $appSecret): string
    {
        // 构建签名字符串，与服务器端保持一致
        $signContent = sprintf(
            "Method=%s&Path=%s&RandomString=%s&TimeStamp=%s&AppKey=%s",
            $method, $path, $randomString, $timestamp, $appKey
        );
        // 使用HMAC-SHA256计算签名，以secret作为密钥
        $signature = hash_hmac('sha256', $signContent, $appSecret);
        
        // 转为大写
        return strtoupper($signature);
    }

    /**
     * 生成随机字符串
     *
     * @param int $length 长度
     * @return string
     */
    public static function generateRandomString(int $length = 20): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * 验证签名
     *
     * @param string $method HTTP方法
     * @param string $path 请求路径
     * @param string $randomString 随机字符串
     * @param int $timestamp 时间戳
     * @param string $appKey AppKey
     * @param string $appSecret AppSecret
     * @param string $sign 签名
     * @return bool
     */
    public static function verifySign(string $method, string $path, string $randomString, int $timestamp, string $appKey, string $appSecret, string $sign): bool
    {
        $generatedSign = self::generateSign($method, $path, $randomString, $timestamp, $appKey, $appSecret);
        return $generatedSign === $sign;
    }
}