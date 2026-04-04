# 电子发票/数电发票 php SDK | 开票、验真、红冲一站式集成
[![Packagist Version](https://img.shields.io/packagist/v/tax/invoice)](https://packagist.org/packages/tax/invoice)
[![License](https://img.shields.io/badge/License-Apache%202.0-blue.svg)](https://github.com/fapiaoapi/invoice-sdk-php/blob/master/LICENSE)

**发票 php SDK** 专为电子发票、数电发票（全电发票）场景设计，支持**开票、红冲、版式文件下载**等核心功能，快速对接税务平台API。

**关键词**: 电子发票SDK,数电票php,开票接口,发票api,发票开具,发票红冲,全电发票集成

---

## 📖 核心功能

### 基础认证
- ✅ **获取授权** - 快速接入税务平台身份认证
- ✅ **人脸二维码登录** - 支持数电发票平台扫码登录
- ✅ **认证状态查询** - 实时获取纳税人身份状态

### 发票开具
- 🟦 **数电蓝票开具** - 支持增值税普通/专用电子发票
- 📄 **版式文件下载** - 自动获取销项发票PDF/OFD/XML文件

### 发票红冲
- 🔍 **红冲前蓝票查询** - 精确检索待红冲的电子发票
- 🛑 **红字信息表申请** - 生成红冲凭证
- 🔄 **负数发票开具** - 自动化红冲流程

---

## 🚀 快速安装

通过Composer安装:

```bash
composer require tax/invoice
```
[📦 查看Packagist最新版本](https://packagist.org/packages/tax/invoice)
---
[📚 查看完整中文文档](https://fa-piao.com/doc.html?source=github) | [💡 更多示例代码](https://github.com/fapiaoapi/invoice-sdk-php/tree/master/examples)

---

## 🔍 为什么选择此SDK？
- **精准覆盖中国数电发票标准** - 严格遵循国家最新接口规范
- **开箱即用** - 无需处理XML/签名等底层细节，专注业务逻辑
- **企业级验证** - 已在生产环境处理超100万张电子发票

---

## 📊 支持的开票类型
| 发票类型       | 状态   |
|----------------|--------|
| 数电发票（普通发票） | ✅ 支持 |
| 数电发票（增值税专用发票） | ✅ 支持 |
| 数电发票（铁路电子客票）  | ✅ 支持 |
| 数电发票（航空运输电子客票行程单） | ✅ 支持  |
| 数电票（二手车销售统一发票） | ✅ 支持  |
| 数电纸质发票（增值税专用发票） | ✅ 支持  |
| 数电纸质发票（普通发票） | ✅ 支持  |
| 数电纸质发票（机动车发票） | ✅ 支持  |
| 数电纸质发票（二手车发票） | ✅ 支持  |

---

## 🤝 贡献与支持
- 提交Issue: [问题反馈](https://github.com/fapiaoapi/invoice-sdk-php/issues)
- 商务合作: yuejianghe@qq.com
```bash
<?php

require_once  'vendor/autoload.php';

use Tax\Invoice\Client;
use Tax\Invoice\Exception\InvoiceException;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;


class Config {
    public static $appKey    = '';
    private static $appSecret = '';
    public static $nsrsbh    = '';//统一社会信用代码
    public static $title     = '';//名称（营业执照）
    public static $username  = '';//手机号码（电子税务局）账号
    public static $password  = '';//个人用户密码（电子税务局）
    public static $type      = '7';// 6基础版7标准版
    public static $xhdwdzdh      = '重庆市渝北区龙溪街道丽园路2号XXXX 1325580XXXX';// 地址和电话 空格隔开
    public static $xhdwyhzh      = '工商银行XXXX 15451211XXXX';// 开户行和银行账号 空格隔开
    
    private static $debug  = true;
    public static $client;
    public static $redis;

    public static function init()
    {
        self::$client = new Client(self::$appKey, self::$appSecret,self::$debug);
        self::$redis = new Redis();
        self::$redis->connect('127.0.0.1', 6379);
        self::$redis->auth('test123456');
    }

}

if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    $scriptName = basename(__FILE__);
    echo "--------------------------------------------------\n";
    echo "❌ 错误：此脚本不支持在 Windows 命令行下直接运行。\n";
    echo "💡 提示：请使用 WSL 或 Git Bash 运行，或部署到 Linux 服务器。\n";
    echo "--------------------------------------------------\n";
    echo "❌ Error: This script is not supported on Windows CLI.\n";
    echo "💡 Hint: Please run via WSL/Git Bash or deploy to a Linux server.\n";
    echo "--------------------------------------------------\n\n";
    exit(1); // 退出脚本，返回错误码 1
}

Config::init();

try {


    // 一 获取授权token  可从缓存redis中获取Token
    getToken();
    /*
     * 前端模拟数电发票/电子发票开具 (蓝字发票)
     * @link https://fa-piao.com/fapiao.html?source=github
     *
     * json2php在线工具可以将前端json数据转换为php数组格式，方便后端开发调试
     * @link https://uutool.cn/json2php/
     */
    //二 开具蓝票
    $invoiceResponse = blueInvoice();
    if ($invoiceResponse['code'] == 200) {
        // 三 获取pdf/ofd/xml
        doloadPdfOfdXml($invoiceResponse['data']['Fphm'], $invoiceResponse['data']['Kprq']);
    }
} catch (InvoiceException $e) {
    switch ($e->getErrorCode()) {
        case 420:
            echo "登录(短信认证)". PHP_EOL;
            /*
             * 前端模拟短信认证弹窗
             * @link https://fa-piao.com/fapiao.html?action=sms&source=github
             */
             // 1. 发短信验证码
            /*
             * @link https://fa-piao.com/doc.html#api2?source=github
             */
            $loginResponse = Config::$client->loginDppt(Config::$nsrsbh, Config::$username, Config::$password, "");
            if ($loginResponse['code'] == 200) {
                echo "请输入验证码".PHP_EOL;
                echo "请在300秒内(".date('Y-m-d H:i:s', time() + 300)."前)输入验证码: ".PHP_EOL;
                $timeout = 300;
                $read = [STDIN];
                $write = $except = null;
                if (stream_select($read, $write, $except, $timeout)) {
                    $input = trim(fgets(STDIN));
                    echo "你输入了：$input\n";
                    // 2. 输入验证码
                    /*
                     * @link https://fa-piao.com/doc.html#api2?source=github
                     */
                    $loginResponse2 = Config::$client->loginDppt(Config::$nsrsbh, Config::$username, Config::$password, $input);
                    if ($loginResponse2['code'] == 200) {
                        echo $loginResponse2['data'] . PHP_EOL;
                        echo "短信验证成功". PHP_EOL;
                        echo "请再次调用blueInvoice". PHP_EOL;
                        $invoiceResponse = blueInvoice();
                        if ($invoiceResponse['code'] == 200) {
                            doloadPdfOfdXml($invoiceResponse['data']['Fphm'], $invoiceResponse['data']['Kprq']);
                        }
                    }
                } else {
                    echo "\n超时！300秒内未输入。\n";
                }

            }
            break;
        case 430:
            echo "人脸认证". PHP_EOL;
            /*
             * 前端模拟人脸认证弹窗
             * @link https://fa-piao.com/fapiao.html?action=face&source=github
             */
            // 1. 获取人脸二维码
            /*
             * @link https://fa-piao.com/doc.html#api3?source=github
             */
           $qrCodeResponse = Config::$client->getFaceImg(Config::$nsrsbh, Config::$username, "1");
           echo $qrCodeResponse['data']['ewmly'] == 'swj' ? "请使用电子税务局app扫码". PHP_EOL : "个人所得税app扫码". PHP_EOL;
           if (isset($qrCodeResponse['data']['ewm']) && strlen($qrCodeResponse['data']['ewm']) < 500) {
//               //composer require chillerlan/php-qrcode 构建二维码并获取 Base64
//               // 生成并返回 Base64 Data URI
//               $qrBase64 = (new QRCode(new QROptions([
//                   'version'      => 7,                       // 版本 7，确保能容纳长字符串
//                   'outputType'   => QRCode::OUTPUT_IMAGE_PNG, // 【关键】输出类型为 PNG 图片
//                   'eccLevel'     => QRCode::ECC_L,           // 低纠错级别，节省空间
//                   'moduleLength' => 6,                       // 模块大小，影响图片尺寸
//                   'imageBase64'  => true,                    // 【关键】直接生成 Base64 格式的 Data URI
//               ])))->render($qrCodeResponse['data']['ewm']);
//               $qrCodeResponse['data']['ewm'] = $qrBase64;
//               // 输出生成的 Base64 字符串 返回给前端
//               // 格式为: data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAA...
           }
            echo "成功做完人脸认证,请输入数字 1". PHP_EOL;
            // 生成二维码图片 并输出到命令行
            echo (new QRCode(new QROptions([
                'version'      => 7,            // 【关键修改】将版本从默认值提高到 10（容量大幅提升）
                'eccLevel'     => QRCode::ECC_M, // 【关键修改】将纠错等级从 H 降到 M（释放空间）
                'outputType'   => QRCode::OUTPUT_STRING_TEXT
            ])))->render($qrCodeResponse['data']['ewm']);
            echo PHP_EOL;
            echo "300秒内(".date('Y-m-d H:i:s', time() + 300)."前)输入内容: ".PHP_EOL;
            $timeout = 300;
            $read = [STDIN];
            $write = $except = null;
            // 等待输入，最多 $timeout 秒
            if (stream_select($read, $write, $except, $timeout)) {
                $input = trim(fgets(STDIN));
                echo "你输入了：$input\n";
                // 2. 认证完成后获取人脸二维码认证状态
                /*
                 * @link https://fa-piao.com/doc.html#api4?source=github
                 */
                $rzid = $qrCodeResponse['data']['rzid'];
                $faceStatusResponse = Config::$client->getFaceState(Config::$nsrsbh, $rzid, Config::$username, "1");
                if ($faceStatusResponse['data'] != null) {
                    switch ($faceStatusResponse['data']['slzt']) {
                        case '1':
                            echo "人脸认证未认证".PHP_EOL;
                            break;
                        case '2':
                            echo "人脸认证成功".PHP_EOL;
                            echo "请再次调用blueInvoice". PHP_EOL;
                            $invoiceResponse = blueInvoice();
                            if ($invoiceResponse['code'] == 200) {
                                doloadPdfOfdXml($invoiceResponse['data']['Fphm'], $invoiceResponse['data']['Kprq']);
                            }
                            break;
                        case '3':
                            echo "人脸认证二维码过期".PHP_EOL;
                            break;
                    }
                }
            } else {
                echo "\n超时！300秒内未输入。\n";
            }
            break;
        case 401:
            //token过期 重新获取并缓存token
            echo $e->getErrorCode() . "授权失败:" . $e->getMessage() . PHP_EOL;
            echo "重新授权 获取token 缓存到redis";
            getToken(true);
            echo "请再次调用blueInvoice". PHP_EOL;
            $invoiceResponse = blueInvoice();
            if ($invoiceResponse['code'] == 200) {
                doloadPdfOfdXml($invoiceResponse['data']['Fphm'], $invoiceResponse['data']['Kprq']);
            }
            break;
        default:
            echo "参数:".json_encode($invoiceParams ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL;
            echo $e->getErrorCode() . " " . $e->getMessage() . PHP_EOL;
            break;
    }
} catch (\Exception $e) {
    echo "系统错误: " . $e->getMessage() . PHP_EOL;
}



function getToken($forceUpdate =false)
{
    $key = Config::$nsrsbh ."@".Config::$username. "@TOKEN";
    if ($forceUpdate) {
        /*
        * 获取授权Token文档
        * @link https://fa-piao.com/doc.html#api1?source=github
        */
        $authResult = Config::$client->getAuthorization(Config::$nsrsbh,Config::$type);
//        $authResult = Config::$client->getAuthorization(Config::$nsrsbh,Config::$type,Config::$username,Config::$password);
        if ($authResult['code'] == 200) {
            $token = $authResult['data']['token'];
            Config::$client->setToken($token);
            Config::$redis->set($key, $token, 3600 * 24 * 30); // 设置过期时间为30天
        }
    }else{
        $token = Config::$redis->get($key);
        if ($token) {
            Config::$client->setToken($token);
            echo "Token From Redis: " . PHP_EOL;
        } else {
            $authResult = Config::$client->getAuthorization(Config::$nsrsbh,Config::$type);
//        $authResult = Config::$client->getAuthorization(Config::$nsrsbh,Config::$type,Config::$username,Config::$password);
            if ($authResult['code'] == 200) {
                $token = $authResult['data']['token'];
                Config::$client->setToken($token);
                Config::$redis->set($key, $token, 3600 * 24 * 30); // 设置过期时间为30天
            }
        }
    }
}

function blueInvoice()
{
    /*
     * 开具数电发票文档
     * @link https://fa-piao.com/doc.html#api6?source=github
     *
     * 开票参数说明demo
     * @link https://github.com/fapiaoapi/invoice-sdk-php/blob/master/examples/tax_example.php
     */
    $invoiceParams = [
        "fpqqlsh" => Config::$appKey . time(),
        "username" => Config::$username,
        "kplx" =>0,
        "fplxdm" =>"82",
        "gfzrrbs" =>"N",
        "ghdwmc" =>"个人",
//        "ghdwsbh" =>"91370100MA23333333",
        "ghdwdzdh" =>"",
        "sfzsgmfdzdh" =>"",
        "ghdwyhzh" =>"",
        "sfzsgmfyhzh" =>"",
        "xhdwmc" =>Config::$title,
        "xhdwsbh" =>Config::$nsrsbh,
        "xhdwdzdh" =>Config::$xhdwdzdh,
        "sfzsxsfdzdh" =>"",
        "xhdwyhzh" =>Config::$xhdwyhzh,
        "sfzsxsfyhzh" =>"",
        "hjje" =>198.02,
        "hjse" =>1.98,
        "jshj" =>200,
        "fyxm" =>[
            [
                "fphxz" =>0,
                "hsbz" =>1,
                "spmc" =>"*软件维护服务*接口服务费",
                "spbm" =>"3040201030000000000",
                "je" =>200,
                "sl" =>0.01,
                "se" =>1.98
            ]
        ],
        "bz" =>"",
        "kpr" =>"张三"
    ];
    /*
     * 数电蓝票开具接口 文档
     * @link https://fa-piao.com/doc.html#api6?source=github
     */
    $invoiceResponse = Config::$client->blueTicket($invoiceParams);
    // if ($invoiceResponse['code'] == 200) {
    //     $fphm = $invoiceResponse['data']['Fphm'];
    //     $kprq = $invoiceResponse['data']['Kprq'];
    //     echo "发票号码: " . $fphm . PHP_EOL;
    //     echo "开票日期: " . $invoiceResponse['data']['Kprq'] . PHP_EOL;
    // }
    return $invoiceResponse;
}

function doloadPdfOfdXml($fphm, $kprq)
{
            /*
        * 获取销项数电版式文件 文档 PDF/OFD/XML
        * @link https://fa-piao.com/doc.html#api7?source=github
        */
        $pdfResponse = Config::$client->getPdfOfdXml(Config::$nsrsbh, $fphm, $kprq, '4', Config::$username);
        if ($pdfResponse['code'] == 200) {
            echo "发票下载成功".PHP_EOL;
            echo json_encode($pdfResponse['data']) . PHP_EOL;
        }

}




```
[发票税额计算demo](examples/tax_example.php "发票税额计算") |
[发票红冲demo](examples/red_invoice_example.php "发票红冲")