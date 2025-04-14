<?php

require_once __DIR__ . '/../vendor/autoload.php';
use Tax\Invoice\Client;
use Tax\Invoice\InvoiceFactory;
use Tax\Invoice\Constants;
use Tax\Invoice\Utils;

// 配置信息
$appKey = 'your_app_key';
$appSecret = 'your_app_secret';

$nsrsbh = '91500112MADFAQ9xxx'; // 统一社会信用代码
$title = "重庆悦江河科技有限公司";//名称（营业执照）
$username = "19122840406";//手机号码（电子税务局）
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
            
             // 1. 发短信验证码
             $loginResponse = $client->loginDppt($nsrsbh, $username, $password, "");
             if ($loginResponse['code'] == 200) {
                 echo $loginResponse['msg'] . "\n";
                 echo "请" . $username . "接收验证码\n";
                 sleep(60); // 等待60秒
             }

             // 2. 输入验证码
             echo "请输入验证码\n";
             $smsCode = ""; // 这里应该获取用户输入的验证码
             $loginResponse2 = $client->loginDppt($nsrsbh, $username, $password, $smsCode);
             if ($loginResponse2['code'] == 200) {
                 echo $loginResponse2['data'] . "\n";
                 echo "验证成功\n";
             }
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
            echo $loginResult['code'] . "授权失败:" . $loginResult['msg'] . "\n";
            break;
        default:
            echo $loginResult['code'] . " " . $loginResult['msg'] . "\n";
            break;
    }
} catch (InvoiceException $e) {
    echo "错误: [" . $e->getErrorCode() . "] " . $e->getMessage() . "\n";
} catch (\Exception $e) {
    echo "系统错误: " . $e->getMessage() . "\n";
}

