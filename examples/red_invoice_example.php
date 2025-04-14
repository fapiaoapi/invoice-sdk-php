<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Tax\Invoice\Client;
use Tax\Invoice\InvoiceFactory;
use Tax\Invoice\Constants;
use Tax\Invoice\Exception\InvoiceException;
use Tax\Invoice\Utils;

// 配置信息
$appKey = 'your_app_key';
$appSecret = 'your_app_secret';
$nsrsbh = '915101820724315989'; // 纳税人识别号
$username = '19122840xxx'; // 手机号码（电子税务局）
$fphm = '25502000000038381718';
$kprq = '2025-04-13 13:35:27';
$token = '';

try {
    // 创建客户端
    $client = new Client($appKey, $appSecret);
    if (!empty($token)) {
        $client->setToken($token);
    } else {
        // 获取授权
        $authResponse = $client->getAuthorization($nsrsbh);
        if ($authResponse['code'] == 200) {
            echo "授权成功，Token: " . $authResponse['data']['token'] . "\n";
        }
    }

    // 1. 数电申请红字前查蓝票信息接口
    $sqyy = '2';
    $queryInvoiceResponse = $client->retInviceMsg( $nsrsbh, $fphm,  $sqyy, $username , $nsrsbh);

    if ($queryInvoiceResponse['code'] == 200) {
        echo "1 可以申请红字\n";
        sleep(2);
        
        // 2. 申请红字信息表
        $applyRedParams = [
            'xhdwsbh' => $nsrsbh,
            'yfphm' => $fphm,
            'username' => $username,
            'sqyy' => '2',
            'chyydm' => '01'
        ];
        $applyRedResponse = $client->applyRedInfo($applyRedParams);

        if ($applyRedResponse['code'] == 200) {
            echo "2 申请红字信息表\n";
            sleep(2);
            
            // 3. 开具红字发票
            $redInvoiceParams = [
                'fpqqlsh' => 'red' . $fphm,
                'username' => $username,
                'xhdwsbh' => $nsrsbh,
                'tzdbh' => $applyRedResponse['data']['xxbbh'],
                'yfphm' => $fphm
            ];
            $redInvoiceResponse = $client->redTicket($redInvoiceParams);

            if ($redInvoiceResponse['code'] == 200) {
                echo "3 负数开具成功\n";
            } else {
                echo $redInvoiceResponse['code'] . "数电票负数开具失败:" . $redInvoiceResponse['msg'] . "\n";
                print_r($redInvoiceResponse['data']);
            }
        } else {
            echo $applyRedResponse['code'] . "申请红字信息表失败:" . $applyRedResponse['msg'] . "\n";
            print_r($applyRedResponse['data']);
        }
    } else {
        echo $queryInvoiceResponse['code'] . "查询发票信息失败:" . $queryInvoiceResponse['msg'] . "\n";
        print_r($queryInvoiceResponse['data']);
    }

} catch (InvoiceException $e) {
    echo "错误: [" . $e->getErrorCode() . "] " . $e->getMessage() . "\n";
} catch (\Exception $e) {
    echo "系统错误: " . $e->getMessage() . "\n";
}