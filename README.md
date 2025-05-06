# 电子发票/数电发票 php SDK | 开票、验真、红冲一站式集成
[![Packagist Version](https://img.shields.io/packagist/v/tax/invoice)](https://packagist.org/packages/tax/invoice)
[![License](https://img.shields.io/badge/License-Apache%202.0-blue.svg)](https://github.com/fapiaoapi/invoice-sdk-java/blob/master/LICENSE)

**发票 Java SDK** 专为电子发票、数电发票（全电发票）场景设计，支持**开票、红冲、版式文件下载**等核心功能，快速对接税务平台API。

**关键词**: 电子发票 SDK, 数电票 Java, 发票开具, 发票红冲, 全电发票集成

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
[📚 查看完整中文文档](https://fa-piao.com/doc.html) | [💡 更多示例代码](https://github.com/fapiaoapi/invoice-sdk-php/tree/master/examples)

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

use Tax\Invoice\Client;
use Tax\Invoice\InvoiceFactory;
use Tax\Invoice\Constants;
use Tax\Invoice\Utils;

// 配置信息
$appKey = 'YOUR_APP_KEY';
$appSecret = 'YOUR_APP_SECRET';

$nsrsbh = '91500112MADFAQ9xxx'; // 统一社会信用代码
$title = "重庆悦江河科技有限公司";//名称（营业执照）
$username = "1912284xxxx";//手机号码（电子税务局）
$password = "";//个人用户密码（电子税务局）
$sf = "01";//身份（电子税务局）
$fphm = "24502000000045823936";
$kprq = "";
$token = "";


try {
    // 创建客户端
    $client = new Client($appKey, $appSecret);
    
    // 获取授权
    if ($token) {
        $client->setToken($token);
    }else{
        $authResult = $client->getAuthorization($nsrsbh);
        if ($authResult['code'] == 200) {
            $token = $authResult['data']['token'];
            $client->setToken($token);
            echo "授权成功，Token: " . $token . PHP_EOL;
        } else {
            echo "授权失败: " . $authResult['msg'] . PHP_EOL;
            exit;
        }
    }
  


    $loginResult = $client->queryFaceAuthState($nsrsbh, $username);
    switch ($loginResult['code']) {
        case 200:
            echo "可以开发票了\n";
             // 税额计算
             $amount = 200;
             $taxRate = 0.01;
             $isIncludeTax = false; // 是否含税
             $se = Utils::calculateTax($amount, $taxRate, $isIncludeTax);
             
             echo "价税合计：" . $amount . "\n";
             echo "税率：" . $taxRate . "\n";
             echo "合计金额：" . ($amount - floatval($se)) . "\n";
             echo ($isIncludeTax ? "含税" : "不含税") . " 合计税额：" . $se . "\n";
            
            // 授信额度查询
            $creditLimitResponse = $client->queryCredit($nsrsbh,$username);
            if ($creditLimitResponse['code'] == 200) {
                print_r($creditLimitResponse['data']);
            }

            // 开具蓝票
            $invoiceParams = [
                'fpqqlsh' => $appKey . time(),
                'fplxdm' => '82',
                'kplx' => '0',
                'xhdwsbh' => $nsrsbh,
                'xhdwmc' => '重庆悦江河科技有限公司',
                'xhdwdzdh' => '重庆市渝北区仙桃街道汇业街1号17-2 19122840xxxx',
                'xhdwyhzh' => '中国工商银行 310008670920023xxxx',
                'ghdwmc' => '个人',
                'zsfs' => '0',
                'fyxm' => [
                    [
                        'fphxz' => '0',
                        'spmc' => '*信息技术服务*软件开发服务',
                        'je' => '10',
                        'sl' => '0.01',
                        'se' => '0.1',
                        'hsbz' => '1',
                        'spbm' => '3040201010000000000'
                    ]
                ],
                'hjje' => '9.9',
                'hjse' => '0.1',
                'jshj' => '10'
            ];

            $invoiceResponse = $client->blueTicket($invoiceParams);
            echo $invoiceResponse['code'] . " 开票结果: " . $invoiceResponse['msg'] . "\n";

            if ($invoiceResponse['code'] == 200) {
                $fphm = $invoiceResponse['data']['Fphm'];
                $kprq = $invoiceResponse['data']['Kprq'];
                echo "发票号码: " . $fphm . "\n";
                echo "开票日期: " . $invoiceResponse['data']['Kprq'] . "\n";
            }

            // 下载发票
                $pdfResponse = $client->getPdfOfdXml($nsrsbh,$fphm,'4',$kprq,$username);
                if ($pdfResponse['code'] == 200) {
                    print_r($pdfResponse['data']);
                }
            break;
        case 420:
            echo "登录(短信认证)\n";
            
            // // 1. 发短信验证码
            // $loginResponse = $client->loginDppt($nsrsbh, $username, $password, "");
            // if ($loginResponse['code'] == 200) {
            //     echo $loginResponse['msg'] . "\n";
            //     echo "请" . $username . "接收验证码\n";
            //     sleep(60); // 等待60秒
            // }

            // // 2. 输入验证码
            // echo "请输入验证码\n";
            // $smsCode = ""; // 这里应该获取用户输入的验证码
            // $loginResponse2 = $client->loginDppt($nsrsbh, $username, $password, $smsCode);
            // if ($loginResponse2['code'] == 200) {
            //     echo $loginResponse2['data'] . "\n";
            //     echo "验证成功\n";
            // }
            break;
        case 430:
            echo "人脸认证\n";
            // 1. 获取人脸二维码
            $qrCodeResponse = $client->getFaceImg($nsrsbh, $username, "1");
            echo "二维码: " . print_r($qrCodeResponse['data'], true) . "\n";
            
            switch ($qrCodeResponse['data']['ewmlyx']) {
                case 'swj':
                    echo "请使用税务局app扫码\n";
                    break;
                case 'grsds':
                    echo "个人所得税app扫码\n";
                    break;
            }

            // 2. 认证完成后获取人脸二维码认证状态
            $rzid = $qrCodeResponse['data']['rzid'];
            $faceStatusResponse = $client->getFaceState($nsrsbh, $rzid, $username, "1");
            echo "code: " . $faceStatusResponse['code'] . "\n";
            echo "data: " . print_r($faceStatusResponse['data'], true) . "\n";
            
            if ($faceStatusResponse['data'] != null) {
                switch ($faceStatusResponse['data']['slzt']) {
                    case '1':
                        echo "未认证\n";
                        break;
                    case '2':
                        echo "成功\n";
                        break;
                    case '3':
                        echo "二维码过期-->重新获取人脸二维码\n";
                        break;
                }
            }
            break;
        case 401:
            echo $statusResponse['code'] . "授权失败:" . $statusResponse['msg'] . "\n";
            break;
        default:
            echo $statusResponse['code'] . " " . $statusResponse['msg'] . "\n";
            break;
    }
} catch (InvoiceException $e) {
    echo "错误: [" . $e->getErrorCode() . "] " . $e->getMessage() . "\n";
} catch (\Exception $e) {
    echo "系统错误: " . $e->getMessage() . "\n";
}

```
[发票红冲demo](examples/red_invoice_example.php "发票红冲")