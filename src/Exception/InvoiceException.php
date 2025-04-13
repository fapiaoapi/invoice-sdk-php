<?php

namespace Tax\Invoice\Exception;

class InvoiceException extends \Exception
{
    /**
     * 错误码
     *
     * @var int
     */
    protected $errorCode;

    /**
     * 构造函数
     *
     * @param string $message 错误信息
     * @param int $code 错误码
     * @param \Throwable|null $previous 上一个异常
     */
    public function __construct(string $message = "", int $code = 0, \Throwable $previous = null)
    {
        $this->errorCode = $code;
        parent::__construct($message, $code, $previous);
    }

    /**
     * 获取错误码
     *
     * @return int
     */
    public function getErrorCode(): int
    {
        return $this->errorCode;
    }

    /**
     * 创建API错误异常
     *
     * @param int $code 错误码
     * @param string $message 错误信息
     * @return InvoiceException
     */
    public static function apiError(int $code, string $message): self
    {
        return new self("API错误 [{$code}]: {$message}", $code);
    }

    /**
     * 需要短信验证
     *
     * @param string $message 错误信息
     * @return InvoiceException
     */
    public static function needSms(string $message): self
    {
        return new self($message, 420);
    }
    /**
     * 需要人脸验证
     *
     * @param string $message 错误信息
     * @return InvoiceException
     */
    public static function needFace(string $message): self
    {
        return new self($message, 430);
    }

    /**
     * 创建参数错误异常
     *
     * @param string $message 错误信息
     * @return InvoiceException
     */
    public static function paramError(string $message): self
    {
        return new self("参数错误: {$message}", 310);
    }

    /**
     * 创建网络错误异常
     *
     * @param string $message 错误信息
     * @return InvoiceException
     */
    public static function networkError(string $message): self
    {
        return new self("网络错误: {$message}", 500);
    }
}