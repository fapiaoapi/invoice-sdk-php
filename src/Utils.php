<?php

namespace Tax\Invoice;

class Utils
{
    /**
     * 生成发票请求流水号
     *
     * @param string $prefix 前缀
     * @return string
     */
    public static function generateInvoiceSerialNumber(string $prefix = 'FP'): string
    {
        $timestamp = date('YmdHis');
        $random = mt_rand(1000, 9999);
        
        return $prefix . $timestamp . $random;
    }

    /**
     * 验证税号
     *
     * @param string $nsrsbh 纳税人识别号
     * @return bool
     */
    public static function validateTaxNumber(string $nsrsbh): bool
    {
        // 统一社会信用代码（18位）
        if (strlen($nsrsbh) == 18) {
            return preg_match('/^[0-9A-Z]{18}$/', $nsrsbh) === 1;
        }
        
        // 纳税人识别号（15位）
        if (strlen($nsrsbh) == 15) {
            return preg_match('/^[0-9]{15}$/', $nsrsbh) === 1;
        }
        
        return false;
    }

    /**
     * 验证手机号
     *
     * @param string $mobile 手机号
     * @return bool
     */
    public static function validateMobile(string $mobile): bool
    {
        return preg_match('/^1[3-9]\d{9}$/', $mobile) === 1;
    }

    /**
     * 验证邮箱
     *
     * @param string $email 邮箱
     * @return bool
     */
    public static function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * 格式化金额（保留2位小数）
     *
     * @param float|string $amount 金额
     * @return string
     */
    public static function formatAmount($amount): string
    {
        return number_format((float)$amount, 2, '.', '');
    }

    /**
     * 计算税额
     *
     * @param float|string $amount 金额
     * @param float|string $taxRate 税率
     * @param bool $isIncludeTax 是否含税
     * @return string
     */
    public static function calculateTax($amount, $taxRate, bool $isIncludeTax = false): string
    {
        $amount = (float)$amount;
        $taxRate = (float)$taxRate;
        
        if ($isIncludeTax) {
            // 含税计算：税额 = 金额 / (1 + 税率) * 税率
            $tax = $amount / (1 + $taxRate) * $taxRate;
        } else {
            // 不含税计算：税额 = 金额 * 税率
            $tax = $amount * $taxRate;
        }
        
        return self::formatAmount($tax);
    }

    /**
     * 计算不含税金额
     *
     * @param float|string $amount 含税金额
     * @param float|string $taxRate 税率
     * @return string
     */
    public static function calculateAmountWithoutTax($amount, $taxRate): string
    {
        $amount = (float)$amount;
        $taxRate = (float)$taxRate;
        
        // 不含税金额 = 含税金额 / (1 + 税率)
        $amountWithoutTax = $amount / (1 + $taxRate);
        
        return self::formatAmount($amountWithoutTax);
    }

    /**
     * 计算含税金额
     *
     * @param float|string $amount 不含税金额
     * @param float|string $taxRate 税率
     * @return string
     */
    public static function calculateAmountWithTax($amount, $taxRate): string
    {
        $amount = (float)$amount;
        $taxRate = (float)$taxRate;
        
        // 含税金额 = 不含税金额 * (1 + 税率)
        $amountWithTax = $amount * (1 + $taxRate);
        
        return self::formatAmount($amountWithTax);
    }

    /**
     * 将金额转换为中文大写
     *
     * @param float|string $amount 金额
     * @return string
     */
    public static function amountToChinese($amount): string
    {
        $amount = (float)$amount;
        
        if ($amount == 0) {
            return '零元整';
        }
        
        $chnNumChar = ['零', '壹', '贰', '叁', '肆', '伍', '陆', '柒', '捌', '玖'];
        $chnUnitChar = ['', '拾', '佰', '仟', '万', '拾', '佰', '仟', '亿', '拾', '佰', '仟', '万', '拾', '佰', '仟'];
        $chnUnitSection = ['', '万', '亿', '万亿'];
        
        $integerPart = intval($amount);
        $decimalPart = round(($amount - $integerPart) * 100);
        
        $chineseStr = '';
        
        // 处理整数部分
        if ($integerPart > 0) {
            $integerStr = (string)$integerPart;
            $integerLen = strlen($integerStr);
            
            $section = 0;
            $sectionPos = 0;
            $zero = true;
            
            for ($i = $integerLen - 1; $i >= 0; $i--) {
                $digit = (int)$integerStr[$integerLen - $i - 1];
                
                if ($digit == 0) {
                    $zero = true;
                } else {
                    if ($zero) {
                        $chineseStr .= $chnNumChar[0];
                    }
                    $zero = false;
                    $chineseStr .= $chnNumChar[$digit] . $chnUnitChar[$i % 16];
                }
                
                $sectionPos++;
                if ($sectionPos == 4) {
                    $section++;
                    $sectionPos = 0;
                    $zero = true;
                    $chineseStr .= $chnUnitSection[$section];
                }
            }
            
            $chineseStr .= '元';
        }
        
        // 处理小数部分
        if ($decimalPart > 0) {
            $jiao = intval($decimalPart / 10);
            $fen = $decimalPart % 10;
            
            if ($jiao > 0) {
                $chineseStr .= $chnNumChar[$jiao] . '角';
            }
            
            if ($fen > 0) {
                $chineseStr .= $chnNumChar[$fen] . '分';
            }
        } else {
            $chineseStr .= '整';
        }
        
        return $chineseStr;
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
     * 将Base64编码的文件保存到本地
     *
     * @param string $base64Content Base64编码的内容
     * @param string $filePath 文件保存路径
     * @return bool
     */
    public static function saveBase64File(string $base64Content, string $filePath): bool
    {
        $fileContent = base64_decode($base64Content);
        if ($fileContent === false) {
            return false;
        }
        
        return file_put_contents($filePath, $fileContent) !== false;
    }
}