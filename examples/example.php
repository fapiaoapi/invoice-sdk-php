<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Tax\Invoice\Client;
use Tax\Invoice\InvoiceFactory;
use Tax\Invoice\Constants;

// 配置信息
$appKey = 'your_app_key';
$appSecret = 'your_app_secret';
$nsrsbh = '915101820724315989'; // 纳税人识别号

try {
    // 创建客户端
    $client = new Client($appKey, $appSecret);
    
    // 获取授权
    $authResult = $client->getAuthorization($nsrsbh);
    if ($authResult['code'] == 200) {
        $token = $authResult['data']['token'];
        $client->setToken($token);
        echo "授权成功，Token: " . $token . PHP_EOL;
    } else {
        echo "授权失败: " . $authResult['msg'] . PHP_EOL;
        exit;
    }

    $loginResult = $client->queryFaceAuthState($nsrsbh, $username);
    switch ($loginResult['code']) {
        case 420:
            // 短信超时
            echo "请先完成短信验证\n";
            exit;
        case 430:
            // 获取人脸二维码 再用税务app或者个税app扫描二维码完成人脸验证
            echo "请先完成人脸验证(通过税务app或者个税app)\n";
            exit; 
        case 200:
                echo "可以开发票了\n";
            break;
    }
} catch (InvoiceException $e) {
    echo "错误: [" . $e->getErrorCode() . "] " . $e->getMessage() . "\n";
} catch (\Exception $e) {
    echo "系统错误: " . $e->getMessage() . "\n";
}
